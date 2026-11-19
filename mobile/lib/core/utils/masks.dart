import 'package:flutter/services.dart';

/// Formata enquanto digita. Só reescreve o texto; a posição do cursor vai para o fim.
abstract class _MascaraDigitos extends TextInputFormatter {
  String formatar(String digitos);
  int get maximo;

  @override
  TextEditingValue formatEditUpdate(TextEditingValue antigo, TextEditingValue novo) {
    var d = novo.text.replaceAll(RegExp(r'\D'), '');
    if (d.length > maximo) d = d.substring(0, maximo);
    final texto = formatar(d);
    return TextEditingValue(text: texto, selection: TextSelection.collapsed(offset: texto.length));
  }
}

String _aplicar(String d, String padrao) {
  final b = StringBuffer();
  var i = 0;
  for (final c in padrao.split('')) {
    if (i >= d.length) break;
    if (c == '#') {
      b.write(d[i++]);
    } else {
      b.write(c);
    }
  }
  return b.toString();
}

/// CPF até 11 dígitos; a partir do 12º vira CNPJ.
class DocumentoMask extends _MascaraDigitos {
  @override
  int get maximo => 14;

  @override
  String formatar(String d) => d.length <= 11 ? _aplicar(d, '###.###.###-##') : _aplicar(d, '##.###.###/####-##');
}

/// (31) 3222-1100 ou (31) 98888-7777.
class TelefoneMask extends _MascaraDigitos {
  @override
  int get maximo => 11;

  @override
  String formatar(String d) => d.length <= 10 ? _aplicar(d, '(##) ####-####') : _aplicar(d, '(##) #####-####');
}
