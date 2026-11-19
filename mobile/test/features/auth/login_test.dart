import 'package:flutter_test/flutter_test.dart';
import 'package:ia_zest/core/di/injector.dart';
import 'package:ia_zest/core/storage/token_storage.dart';

import '../../helpers/api_falsa.dart';

void main() {
  late ApiFalsa api;
  late MemoryTokenStorage tokens;
  late Injector di;

  setUp(() {
    api = ApiFalsa();
    tokens = MemoryTokenStorage();
    di = Injector(apiUrl: 'http://api', tokens: tokens, httpClient: api.client);
  });

  test('login certo guarda o token e devolve o usuário', () async {
    final c = di.loginController();
    expect(await c.entrar('DEMO@iazest.dev ', 'Demo@2026'), isTrue);
    expect(c.usuario!.primeiroNome, 'Usuário');
    expect(tokens.token, 'jwt-valido');
    expect(c.erro, isNull);
  });

  test('senha errada mostra a mensagem da API e não guarda token', () async {
    final c = di.loginController();
    expect(await c.entrar('demo@iazest.dev', 'errada'), isFalse);
    expect(c.erro, 'E-mail ou senha incorretos.');
    expect(tokens.token, isNull);
  });

  test('e-mail inválido nem chama a API', () async {
    final c = di.loginController();
    expect(await c.entrar('nao-e-email', 'x'), isFalse);
    expect(c.erro, 'E-mail inválido.');
    expect(api.chamadas, 0);
  });

  test('sessão salva é reaproveitada; token inválido é apagado', () async {
    tokens.token = 'jwt-valido';
    expect((await di.auth.sessaoAtual())?.email, 'demo@iazest.dev');

    tokens.token = 'vencido';
    expect(await di.auth.sessaoAtual(), isNull);
    expect(tokens.token, isNull);
  });
}
