import 'package:flutter_test/flutter_test.dart';
import 'package:ia_zest/core/utils/validators.dart';

void main() {
  group('documento', () {
    test('aceita CPF e CNPJ válidos, com ou sem máscara', () {
      expect(Validators.documento('529.982.247-25'), isNull);
      expect(Validators.documento('52998224725'), isNull);
      expect(Validators.documento('11.222.333/0001-81'), isNull);
    });

    test('recusa dígito verificador errado, repetidos e tamanho errado', () {
      for (final v in ['529.982.247-26', '111.111.111-11', '11.222.333/0001-82', '00000000000000', '123', '']) {
        expect(Validators.documento(v), isNotNull, reason: v);
      }
    });
  });

  test('email', () {
    expect(Validators.email('demo@iazest.dev'), isNull);
    expect(Validators.email(''), 'Informe o e-mail.');
    expect(Validators.email('sem-arroba'), 'E-mail inválido.');
  });

  test('telefone exige DDD (10 ou 11 dígitos)', () {
    expect(Validators.telefone('(31) 98888-7777'), isNull);
    expect(Validators.telefone('(31) 3222-1100'), isNull);
    expect(Validators.telefone('9888-7777'), isNotNull);
  });

  test('uf é opcional, mas se vier precisa existir', () {
    expect(Validators.uf(''), isNull);
    expect(Validators.uf('mg'), isNull);
    expect(Validators.uf('XX'), 'UF inválida.');
  });

  test('nome mínimo de 3 letras', () {
    expect(Validators.nome('Al'), isNotNull);
    expect(Validators.nome('Ana'), isNull);
  });
}
