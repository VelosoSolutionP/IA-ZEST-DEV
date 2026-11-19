import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

/// API em memória com o mesmo contrato da API PHP (para testes sem rede).
class ApiFalsa {
  final clientes = <Map<String, dynamic>>[
    {'id': 'c-1', 'nome': 'Padaria Pão Quente Ltda', 'tipo_pessoa': 'PJ', 'documento': '11.222.333/0001-81', 'email': 'p@q.dev', 'telefone': '3132221100', 'cidade': 'Belo Horizonte', 'uf': 'MG'},
  ];
  int chamadas = 0;

  late final http.Client client = MockClient((req) async {
    chamadas++;
    final autorizado = req.headers['Authorization'] == 'Bearer jwt-valido';
    final p = req.url.path;
    if (p == '/api/v1/auth/login') {
      final b = jsonDecode(req.body) as Map;
      if (b['email'] == 'demo@iazest.dev' && b['senha'] == 'Demo@2026') {
        return _json({'token': 'jwt-valido', 'expira_em': 0, 'usuario': {'id': 'u-1', 'nome': 'Usuário Demo', 'email': 'demo@iazest.dev'}});
      }
      return _json({'erro': 'E-mail ou senha incorretos.'}, 401);
    }
    if (!autorizado) return _json({'erro': 'Faça login para continuar.'}, 401);
    if (p == '/api/v1/me') return _json({'id': 'u-1', 'nome': 'Usuário Demo', 'email': 'demo@iazest.dev'});
    if (p == '/api/v1/clientes' && req.method == 'GET') return _json({'itens': clientes, 'proximo': null});
    if (p == '/api/v1/clientes' && req.method == 'POST') {
      final b = jsonDecode(req.body) as Map<String, dynamic>;
      final doc = (b['documento'] as String).replaceAll(RegExp(r'\D'), '');
      if (clientes.any((c) => (c['documento'] as String).replaceAll(RegExp(r'\D'), '') == doc)) {
        return _json({'erro': 'Já existe cliente com este CPF/CNPJ.'}, 409);
      }
      final novo = {...b, 'id': 'c-${clientes.length + 1}', 'tipo_pessoa': doc.length == 11 ? 'PF' : 'PJ'};
      clientes.insert(0, novo);
      return _json(novo, 201);
    }
    return _json({'erro': 'Rota não encontrada.'}, 404);
  });

  static http.Response _json(Object corpo, [int status = 200]) => http.Response.bytes(
        utf8.encode(jsonEncode(corpo)),
        status,
        headers: {'content-type': 'application/json; charset=utf-8'},
      );
}
