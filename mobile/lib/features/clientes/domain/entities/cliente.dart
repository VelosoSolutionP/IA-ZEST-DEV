class Cliente {
  const Cliente({
    required this.id,
    required this.nome,
    required this.tipoPessoa,
    required this.documento,
    required this.email,
    required this.telefone,
    this.cidade,
    this.uf,
  });

  final String id;
  final String nome;
  final String tipoPessoa; // PF | PJ
  final String documento; // já formatado pela API
  final String email;
  final String telefone;
  final String? cidade;
  final String? uf;

  String get iniciais {
    final partes = nome.trim().split(RegExp(r'\s+'));
    return (partes.first[0] + (partes.length > 1 ? partes[1][0] : '')).toUpperCase();
  }

  String? get local => [cidade, uf].whereType<String>().where((s) => s.isNotEmpty).join(' - ').ifEmptyNull;
}

class NovoCliente {
  const NovoCliente({
    required this.nome,
    required this.documento,
    required this.email,
    required this.telefone,
    this.cidade,
    this.uf,
  });

  final String nome;
  final String documento;
  final String email;
  final String telefone;
  final String? cidade;
  final String? uf;
}

class PaginaClientes {
  const PaginaClientes(this.itens, this.proximo);
  final List<Cliente> itens;
  final String? proximo;
}

extension on String {
  String? get ifEmptyNull => isEmpty ? null : this;
}
