import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:krishi_sathi/core/network/api_client.dart';

typedef Handler = Future<ResponseBody> Function(RequestOptions options);

/// Dio adapter that answers from a function instead of the network.
class FakeAdapter implements HttpClientAdapter {
  FakeAdapter(this.handler);
  Handler handler;
  final List<RequestOptions> requests = [];

  @override
  Future<ResponseBody> fetch(RequestOptions options, Stream<Uint8List>? requestStream, Future<void>? cancelFuture) {
    requests.add(options);
    return handler(options);
  }

  @override
  void close({bool force = false}) {}
}

ResponseBody json(Object data, {int status = 200, Map<String, List<String>> headers = const {}}) => ResponseBody.fromString(
      jsonEncode(data),
      status,
      headers: {
        Headers.contentTypeHeader: [Headers.jsonContentType],
        ...headers,
      },
    );

Future<ResponseBody> offline(RequestOptions o) async =>
    throw DioException.connectionError(requestOptions: o, reason: 'offline (test)');

/// ApiClient wired to a [FakeAdapter].
(ApiClient, FakeAdapter) fakeApiClient(Handler handler, {String? Function()? token}) {
  final adapter = FakeAdapter(handler);
  final dio = Dio(BaseOptions(validateStatus: (s) => s != null && (s < 300 || s == 304)))..httpClientAdapter = adapter;
  final client = ApiClient(baseUrl: () => 'http://test/api/v1', token: token ?? () => null, language: () => 'bn', dio: dio);
  return (client, adapter);
}
