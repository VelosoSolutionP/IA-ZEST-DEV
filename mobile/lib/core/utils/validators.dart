/// Validações iguais às da API (a API valida de novo: o app só adianta o aviso).
abstract final class Validators {
  static String digitos(String v) => v.replaceAll(RegExp(r'\D'), '');

  static String? email(String? v) {
    final t = (v ?? '').trim();
    if (t.isEmpty) return 'Informe o e-mail.';
    return RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(t) ? null : 'E-mail inválido.';
  }

  static String? senha(String? v) => (v ?? '').isEmpty ? 'Informe a senha.' : null;

  static String? nome(String? v) {
    final t = (v ?? '').trim();
    return t.length < 3 ? 'Informe o nome (mínimo 3 letras).' : null;
  }

  static String? telefone(String? v) {
    final d = digitos(v ?? '');
    return d.length == 10 || d.length == 11 ? null : 'Telefone com DDD.';
  }

  static String? uf(String? v) {
    final t = (v ?? '').trim().toUpperCase();
    if (t.isEmpty) return null;
    return _ufs.contains(t) ? null : 'UF inválida.';
  }

  static String? documento(String? v) {
    final d = digitos(v ?? '');
    final ok = switch (d.length) { 11 => cpfValido(d), 14 => cnpjValido(d), _ => false };
    return ok ? null : 'CPF ou CNPJ inválido.';
  }

  static bool cpfValido(String d) {
    if (d.length != 11 || RegExp(r'^(\d)\1{10}$').hasMatch(d)) return false;
    for (var t = 9; t < 11; t++) {
      var soma = 0;
      for (var i = 0; i < t; i++) {
        soma += int.parse(d[i]) * ((t + 1) - i);
      }
      if (int.parse(d[t]) != ((10 * soma) % 11) % 10) return false;
    }
    return true;
  }

  static bool cnpjValido(String d) {
    if (d.length != 14 || RegExp(r'^(\d)\1{13}$').hasMatch(d)) return false;
    const pesos = [
      [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
      [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
    ];
    for (var n = 0; n < 2; n++) {
      var soma = 0;
      for (var i = 0; i < pesos[n].length; i++) {
        soma += int.parse(d[i]) * pesos[n][i];
      }
      final resto = soma % 11;
      if (int.parse(d[12 + n]) != (resto < 2 ? 0 : 11 - resto)) return false;
    }
    return true;
  }

  static const _ufs = {
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', //
    'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
  };
}
