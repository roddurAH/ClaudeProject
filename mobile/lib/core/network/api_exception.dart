/// Every failure from the API is turned into one of these, so screens can
/// show a clear, translated message instead of a technical error.
enum ApiErrorType { network, unauthorized, forbidden, notFound, validation, rateLimited, server, wrongServer, unknown }

class ApiException implements Exception {
  ApiException(this.type, {this.message, this.statusCode, this.fieldErrors = const {}});

  final ApiErrorType type;
  final String? message;
  final int? statusCode;

  /// Laravel validation errors: field → messages.
  final Map<String, List<String>> fieldErrors;

  bool get isNetwork => type == ApiErrorType.network;

  /// First validation message, or the general message.
  String? get firstMessage {
    for (final messages in fieldErrors.values) {
      if (messages.isNotEmpty) return messages.first;
    }
    return message;
  }

  @override
  String toString() => 'ApiException($type, $statusCode, $message)';
}
