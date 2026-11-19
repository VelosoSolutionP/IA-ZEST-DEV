import '../../../../core/network/api_exception.dart';
import '../../../../core/storage/token_storage.dart';
import '../../domain/entities/usuario.dart';
import '../../domain/repositories/auth_repository.dart';
import '../datasources/auth_remote_datasource.dart';

class AuthRepositoryImpl implements AuthRepository {
  const AuthRepositoryImpl(this._remoto, this._tokens);

  final AuthRemoteDataSource _remoto;
  final TokenStorage _tokens;

  @override
  Future<Usuario> entrar({required String email, required String senha}) async {
    final r = await _remoto.login(email, senha);
    await _tokens.salvar(r.token);
    return r.usuario;
  }

  @override
  Future<Usuario?> sessaoAtual() async {
    if (await _tokens.ler() == null) return null;
    try {
      return await _remoto.eu();
    } on ApiException catch (e) {
      if (e.naoAutorizado) await _tokens.apagar();
      return null;
    }
  }

  @override
  Future<void> sair() => _tokens.apagar();
}
