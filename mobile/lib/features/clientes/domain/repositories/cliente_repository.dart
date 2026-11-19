import '../entities/cliente.dart';

abstract interface class ClienteRepository {
  Future<Cliente> cadastrar(NovoCliente novo);

  Future<PaginaClientes> listar({String? busca, String? apos});
}
