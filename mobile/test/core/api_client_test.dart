import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:ia_zest/core/network/api_client.dart';
import 'package:ia_zest/core/network/api_exception.dart';
import 'package:ia_zest/core/storage/token_storage.dart';

void main() {
  test('envia o JWT salvo no cabeçalho Authorization', () async {
    String? recebido;
    final tokens = MemoryTokenStorage()..token = 'abc';
    final api = ApiClient(
      baseUrl: 'http://api',
      tokens: tokens,
      client: MockClient((r) async {
        recebido = r.headers['Authorization'];
        return http.Response('{"ok":true}', 200);
      }),
    );
    expect(await api.get('/x'), {'ok': true});
    expect(recebido, 'Bearer abc');
  });

  test('422 vira ApiException com erros por campo', () async {
    final api = ApiClient(
      baseUrl: 'http://api',
      tokens: MemoryTokenStorage(),
      client: MockClient((_) async => http.Response('{"erro":"Dados inválidos.","campos":{"email":"E-mail inválido."}}', 422,
          headers: {'content-type': 'application/json; charset=utf-8'})),
    );
    await expectLater(
      api.post('/x', {}),
      throwsA(isA<ApiException>().having((e) => e.campos['email'], 'campo', 'E-mail inválido.')),
    );
  });

  test('401 numa rota privada avisa que a sessão expirou', () async {
    var expirou = false;
    final api = ApiClient(
      baseUrl: 'http://api',
      tokens: MemoryTokenStorage(),
      client: MockClient((_) async => http.Response('{"erro":"Token inválido."}', 401)),
      aoExpirarSessao: () => expirou = true,
    );
    await expectLater(api.get('/x'), throwsA(isA<ApiException>().having((e) => e.naoAutorizado, '401', true)));
    expect(expirou, isTrue);
  });

  test('falha de rede vira mensagem amigável (status 0)', () async {
    final api = ApiClient(
      baseUrl: 'http://api',
      tokens: MemoryTokenStorage(),
      client: MockClient((_) async => throw http.ClientException('conexão recusada')),
    );
    await expectLater(api.get('/x'), throwsA(isA<ApiException>().having((e) => e.semConexao, 'rede', true)));
  });

  test('ignora parâmetros vazios na query', () async {
    Uri? uri;
    final api = ApiClient(
      baseUrl: 'http://api',
      tokens: MemoryTokenStorage(),
      client: MockClient((r) async {
        uri = r.url;
        return http.Response('{}', 200);
      }),
    );
    await api.get('/clientes', query: {'busca': 'pad', 'apos': null, 'x': ''});
    expect(uri.toString(), 'http://api/clientes?busca=pad');
  });
}
