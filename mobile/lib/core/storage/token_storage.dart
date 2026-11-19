import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Guarda o JWT. No Android usa o Keystore (criptografado), nunca texto puro.
abstract interface class TokenStorage {
  Future<String?> ler();
  Future<void> salvar(String token);
  Future<void> apagar();
}

class SecureTokenStorage implements TokenStorage {
  SecureTokenStorage([FlutterSecureStorage? storage]) : _storage = storage ?? const FlutterSecureStorage();

  static const _chave = 'ia_zest_jwt';
  final FlutterSecureStorage _storage;

  @override
  Future<String?> ler() => _storage.read(key: _chave);

  @override
  Future<void> salvar(String token) => _storage.write(key: _chave, value: token);

  @override
  Future<void> apagar() => _storage.delete(key: _chave);
}

class MemoryTokenStorage implements TokenStorage {
  String? token;

  @override
  Future<String?> ler() async => token;

  @override
  Future<void> salvar(String token) async => this.token = token;

  @override
  Future<void> apagar() async => token = null;
}
