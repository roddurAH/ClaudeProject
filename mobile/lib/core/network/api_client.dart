import 'package:dio/dio.dart';

import 'api_exception.dart';

class ApiResponse {
  ApiResponse(this.statusCode, this.data, this.headers);
  final int statusCode;
  final dynamic data;
  final Headers headers;

  bool get notModified => statusCode == 304;
}

/// Thin wrapper around Dio: base URL, auth token, language header, timeouts
/// and error mapping. Values are read through callbacks so a changed server
/// address or a fresh login takes effect immediately.
class ApiClient {
  ApiClient({
    required this.baseUrl,
    required this.token,
    required this.language,
    Dio? dio,
  }) : _dio = dio ??
            Dio(BaseOptions(
              connectTimeout: const Duration(seconds: 15),
              receiveTimeout: const Duration(seconds: 30),
              sendTimeout: const Duration(seconds: 60),
              // 304 Not Modified is a normal answer for cached content.
              validateStatus: (status) => status != null && (status < 300 || status == 304),
            ));

  final String Function() baseUrl;
  final String? Function() token;
  final String Function() language;
  final Dio _dio;

  /// Called when the server rejects the token (e.g. account disabled).
  void Function()? onUnauthorized;

  Future<ApiResponse> get(String path, {Map<String, dynamic>? query, Map<String, String>? headers}) =>
      _send('GET', path, query: query, headers: headers);

  Future<ApiResponse> post(String path, {Object? data}) => _send('POST', path, data: data);

  Future<ApiResponse> patch(String path, {Object? data}) => _send('PATCH', path, data: data);

  Future<ApiResponse> delete(String path, {Object? data}) => _send('DELETE', path, data: data);

  /// Multipart upload (used for question photos). Works on web and mobile.
  Future<ApiResponse> postMultipart(String path, Map<String, dynamic> fields,
      {List<int>? fileBytes, String fileField = 'image', String fileName = 'photo.jpg'}) {
    final form = FormData.fromMap({
      ...fields..removeWhere((_, v) => v == null),
      if (fileBytes != null) fileField: MultipartFile.fromBytes(fileBytes, filename: fileName),
    });
    return _send('POST', path, data: form);
  }

  Future<ApiResponse> _send(String method, String path,
      {Map<String, dynamic>? query, Object? data, Map<String, String>? headers}) async {
    final bearer = token();
    final base = baseUrl().trim();
    if (base.isEmpty) {
      // No server configured yet: behave exactly like being offline.
      throw ApiException(ApiErrorType.network, message: 'No server address configured');
    }
    try {
      final response = await _dio.request<dynamic>(
        '${base.replaceAll(RegExp(r'/+$'), '')}$path',
        queryParameters: query?..removeWhere((_, v) => v == null),
        data: data,
        options: Options(method: method, headers: {
          'Accept': 'application/json',
          'Accept-Language': language(),
          if (bearer != null) 'Authorization': 'Bearer $bearer',
          ...?headers,
        }),
      );
      final body = response.data;
      if (body is String && body.trim().isNotEmpty) {
        // An HTML page (another web server, a router, a captive portal) instead of our JSON.
        throw ApiException(ApiErrorType.wrongServer, statusCode: response.statusCode, message: 'Not a Krishi Sathi API response');
      }
      return ApiResponse(response.statusCode ?? 200, body, response.headers);
    } on DioException catch (e) {
      throw _map(e);
    }
  }

  ApiException _map(DioException e) {
    switch (e.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
      case DioExceptionType.connectionError:
        return ApiException(ApiErrorType.network, message: e.message);
      case DioExceptionType.badResponse:
        final status = e.response?.statusCode ?? 0;
        final body = e.response?.data;
        final message = body is Map ? body['message']?.toString() : null;
        final errors = <String, List<String>>{};
        if (body is Map && body['errors'] is Map) {
          (body['errors'] as Map).forEach((k, v) {
            errors[k.toString()] = v is List ? v.map((m) => m.toString()).toList() : [v.toString()];
          });
        }
        // A missing route means the address is not the API base (…/api/v1).
        final wrongAddress = (status == 404 || status == 405) &&
            (body is! Map || (message ?? '').startsWith('The route ') || (message ?? '').contains('method is not supported'));
        final type = wrongAddress ? ApiErrorType.wrongServer : switch (status) {
          401 => ApiErrorType.unauthorized,
          403 => ApiErrorType.forbidden,
          404 => ApiErrorType.notFound,
          422 => ApiErrorType.validation,
          429 => ApiErrorType.rateLimited,
          >= 500 => ApiErrorType.server,
          _ => ApiErrorType.unknown,
        };
        if (type == ApiErrorType.unauthorized || type == ApiErrorType.forbidden) {
          onUnauthorized?.call();
        }
        return ApiException(type, statusCode: status, message: message, fieldErrors: errors);
      default:
        // Includes web CORS failures and an unreachable server address.
        return ApiException(ApiErrorType.network, message: e.message);
    }
  }
}
