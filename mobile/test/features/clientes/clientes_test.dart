import 'package:flutter_test/flutter_test.dart';
import 'package:ia_zest/core/di/injector.dart';
import 'package:ia_zest/core/storage/token_storage.dart';
import 'package:ia_zest/features/clientes/domain/entities/cliente.dart';

import '../../helpers/api_falsa.dart';

NovoCliente novo({String doc = '529.982.247-25', String email = 'maria@exemplo.dev'}) =>
    NovoCliente(nome: 'Maria Souza', documento: doc, email: email, telefone: '(31) 98888-7777', cidade: 'Contagem', uf: 'mg');

void main() {
  late ApiFalsa api;
  late Injector di;

  setUp(() {
    api = ApiFalsa();
    di = Injector(apiUrl: 'http://api', tokens: MemoryTokenStorage()..token = 'jwt-valido', httpClient: api.client);
  });

  test('cadastro válido devolve o cliente criado', () async {
    final c = di.cadastroController();
    final cliente = await c.salvar(novo());
    expect(cliente!.tipoPessoa, 'PF');
    expect(cliente.iniciais, 'MS');
    expect(api.clientes.first['uf'], 'MG');
  });

  test('validação local junta os erros e não chama a API', () async {
    final c = di.cadastroController();
    expect(await c.salvar(novo(doc: '123', email: 'x')), isNull);
    expect(c.errosCampos.keys, containsAll(['documento', 'email']));
    expect(api.chamadas, 0);
  });

  test('documento já cadastrado mostra o erro 409 da API', () async {
    final c = di.cadastroController();
    expect(await c.salvar(novo(doc: '11.222.333/0001-81')), isNull);
    expect(c.erro, 'Já existe cliente com este CPF/CNPJ.');
  });

  test('lista carrega e cliente novo entra no topo', () async {
    final c = di.clientesController();
    await c.recarregar();
    expect(c.itens.single.nome, 'Padaria Pão Quente Ltda');
    expect(c.temMais, isFalse);
    c.adicionarNoTopo(const Cliente(id: 'x', nome: 'Novo', tipoPessoa: 'PF', documento: '', email: '', telefone: ''));
    expect(c.itens.first.nome, 'Novo');
  });
}
