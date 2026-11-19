import '../../../../core/utils/validators.dart';
import '../entities/cliente.dart';
import '../repositories/cliente_repository.dart';

class ClienteInvalido implements Exception {
  const ClienteInvalido(this.campos);
  final Map<String, String> campos;
}

class CadastrarClienteUseCase {
  const CadastrarClienteUseCase(this._repo);
  final ClienteRepository _repo;

  Future<Cliente> call(NovoCliente n) {
    final erros = <String, String>{
      'nome': ?Validators.nome(n.nome),
      'documento': ?Validators.documento(n.documento),
      'email': ?Validators.email(n.email),
      'telefone': ?Validators.telefone(n.telefone),
      'uf': ?Validators.uf(n.uf),
    };
    if (erros.isNotEmpty) throw ClienteInvalido(erros);
    return _repo.cadastrar(n);
  }
}
