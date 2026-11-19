import '../../../../core/network/api_client.dart';
import '../../domain/entities/usuario.dart';

class AuthRemoteDataSource {
  const AuthRemoteDataSource(this._api);
  final ApiClient _api;

  Future<({String token, Usuario usuario})> login(String email, String senha) async {
    final r = await _api.post('/api/v1/auth/login', {'email': email, 'senha': senha}, privado: false);
    return (token: r['token'] as String, usuario: usuarioDeJson(r['usuario'] as Map<String, dynamic>));
  }

  Future<Usuario> eu() async => usuarioDeJson(await _api.get('/api/v1/me'));

  static Usuario usuarioDeJson(Map<String, dynamic> j) =>
      Usuario(id: j['id'] as String, nome: j['nome'] as String, email: j['email'] as String);
}
