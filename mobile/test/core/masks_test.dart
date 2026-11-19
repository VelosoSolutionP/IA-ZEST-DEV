import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:ia_zest/core/utils/masks.dart';

String aplicar(TextInputFormatter f, String texto) =>
    f.formatEditUpdate(TextEditingValue.empty, TextEditingValue(text: texto)).text;

void main() {
  test('documento vira CPF até 11 dígitos e CNPJ depois disso', () {
    final m = DocumentoMask();
    expect(aplicar(m, '529982'), '529.982');
    expect(aplicar(m, '52998224725'), '529.982.247-25');
    expect(aplicar(m, '11222333000181'), '11.222.333/0001-81');
    expect(aplicar(m, '11222333000181999'), '11.222.333/0001-81');
  });

  test('telefone fixo e celular', () {
    final m = TelefoneMask();
    expect(aplicar(m, '3132221100'), '(31) 3222-1100');
    expect(aplicar(m, '31988887777'), '(31) 98888-7777');
    expect(aplicar(m, 'ab31'), '(31');
  });
}
