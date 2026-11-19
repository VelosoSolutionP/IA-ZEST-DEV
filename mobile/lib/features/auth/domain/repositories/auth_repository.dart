import '../entities/usuario.dart';

abstract interface class AuthRepository {
  Future<Usuario> entrar({required String email, required String senha});

  /// Usuário da sessão salva, ou null se não houver token válido.
  Future<Usuario?> sessaoAtual();

  Future<void> sair();
}
