import '../../../../core/utils/validators.dart';
import '../entities/usuario.dart';
import '../repositories/auth_repository.dart';

class DadosLoginInvalidos implements Exception {
  const DadosLoginInvalidos(this.mensagem);
  final String mensagem;
}

class LoginUseCase {
  const LoginUseCase(this._repo);
  final AuthRepository _repo;

  Future<Usuario> call({required String email, required String senha}) {
    final erro = Validators.email(email) ?? Validators.senha(senha);
    if (erro != null) throw DadosLoginInvalidos(erro);
    return _repo.entrar(email: email.trim().toLowerCase(), senha: senha);
  }
}
