class Usuario {
  const Usuario({required this.id, required this.nome, required this.email});

  final String id;
  final String nome;
  final String email;

  String get primeiroNome => nome.split(' ').first;
}
