import '../entities/cliente.dart';
import '../repositories/cliente_repository.dart';

class ListarClientesUseCase {
  const ListarClientesUseCase(this._repo);
  final ClienteRepository _repo;

  Future<PaginaClientes> call({String? busca, String? apos}) {
    final b = busca?.trim();
    return _repo.listar(busca: (b == null || b.isEmpty) ? null : b, apos: apos);
  }
}
