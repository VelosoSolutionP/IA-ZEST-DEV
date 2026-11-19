/// Erro vindo da API (ou da rede), já com mensagem pronta para a tela.
class ApiException implements Exception {
  const ApiException(this.status, this.mensagem, {this.campos = const {}});

  /// 0 = sem conexão.
  final int status;
  final String mensagem;

  /// Erros por campo (HTTP 422).
  final Map<String, String> campos;

  bool get naoAutorizado => status == 401;
  bool get semConexao => status == 0;

  @override
  String toString() => 'ApiException($status, $mensagem)';
}
