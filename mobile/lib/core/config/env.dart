/// Configuração de build. Trocar sem mexer no código:
/// `flutter build apk --dart-define=API_URL=https://api.seudominio.com.br`
abstract final class Env {
  static const apiUrl = String.fromEnvironment('API_URL', defaultValue: 'http://10.0.2.2:8091');
  static const timeout = Duration(seconds: 15);
}
