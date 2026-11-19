import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/env.dart';
import '../storage/token_storage.dart';
import 'api_exception.dart';

/// Cliente HTTP único do app: põe o JWT, converte JSON e traduz erros.
class ApiClient {
  ApiClient({required this.baseUrl, required this.tokens, http.Client? client, this.aoExpirarSessao})
      : _http = client ?? http.Client();

  final String baseUrl;
  final TokenStorage tokens;
  final http.Client _http;

  /// Chamado quando a API responde 401 numa rota privada (token vencido).
  void Function()? aoExpirarSessao;

  Future<Map<String, dynamic>> get(String caminho, {Map<String, String?> query = const {}}) {
    final filtrado = {for (final e in query.entries) if (e.value != null && e.value!.isNotEmpty) e.key: e.value!};
    final uri = Uri.parse('$baseUrl$caminho').replace(queryParameters: filtrado.isEmpty ? null : filtrado);
    return _enviar(() async => _http.get(uri, headers: await _cabecalhos()));
  }

  Future<Map<String, dynamic>> post(String caminho, Map<String, dynamic> corpo, {bool privado = true}) {
    final uri = Uri.parse('$baseUrl$caminho');
    return _enviar(
      () async => _http.post(uri, headers: await _cabecalhos(privado: privado), body: jsonEncode(corpo)),
      privado: privado,
    );
  }

  Future<Map<String, String>> _cabecalhos({bool privado = true}) async {
    final token = privado ? await tokens.ler() : null;
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  Future<Map<String, dynamic>> _enviar(Future<http.Response> Function() chamada, {bool privado = true}) async {
    final http.Response r;
    try {
      r = await chamada().timeout(Env.timeout);
    } on TimeoutException {
      throw const ApiException(0, 'O servidor demorou para responder. Tente de novo.');
    } on http.ClientException {
      throw const ApiException(0, 'Sem conexão com o servidor.');
    }

    final corpo = _decodificar(r.body);
    if (r.statusCode >= 200 && r.statusCode < 300) return corpo;

    if (r.statusCode == 401 && privado) aoExpirarSessao?.call();
    final campos = (corpo['campos'] as Map?)?.map((k, v) => MapEntry('$k', '$v')) ?? const <String, String>{};
    throw ApiException(r.statusCode, (corpo['erro'] as String?) ?? 'Erro inesperado (${r.statusCode}).', campos: campos);
  }

  Map<String, dynamic> _decodificar(String body) {
    if (body.isEmpty) return {};
    try {
      final v = jsonDecode(body);
      return v is Map<String, dynamic> ? v : {'dados': v};
    } on FormatException {
      return {};
    }
  }
}
