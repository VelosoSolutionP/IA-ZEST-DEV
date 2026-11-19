import '../../domain/entities/cliente.dart';
import '../../domain/repositories/cliente_repository.dart';
import '../datasources/cliente_remote_datasource.dart';

class ClienteRepositoryImpl implements ClienteRepository {
  const ClienteRepositoryImpl(this._remoto);
  final ClienteRemoteDataSource _remoto;

  @override
  Future<Cliente> cadastrar(NovoCliente novo) async => clienteDeJson(await _remoto.criar(novo));

  @override
  Future<PaginaClientes> listar({String? busca, String? apos}) async {
    final r = await _remoto.listar(busca: busca, apos: apos);
    final itens = (r['itens'] as List).cast<Map<String, dynamic>>().map(clienteDeJson).toList();
    return PaginaClientes(itens, r['proximo'] as String?);
  }

  static Cliente clienteDeJson(Map<String, dynamic> j) => Cliente(
        id: j['id'] as String,
        nome: j['nome'] as String,
        tipoPessoa: j['tipo_pessoa'] as String,
        documento: j['documento'] as String,
        email: j['email'] as String,
        telefone: j['telefone'] as String,
        cidade: j['cidade'] as String?,
        uf: j['uf'] as String?,
      );
}
