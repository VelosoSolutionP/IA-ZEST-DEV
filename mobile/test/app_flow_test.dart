import 'package:flutter_test/flutter_test.dart';
import 'package:ia_zest/app.dart';
import 'package:ia_zest/core/di/injector.dart';
import 'package:ia_zest/core/storage/token_storage.dart';
import 'package:material_ui/material_ui.dart';

import 'helpers/api_falsa.dart';

void main() {
  testWidgets('login com erro, login certo, lista, cadastro e volta com o novo cliente', (tester) async {
    tester.view.physicalSize = const Size(1080, 2340);
    tester.view.devicePixelRatio = 2.6;
    addTearDown(tester.view.reset);

    final api = ApiFalsa();
    final di = Injector(apiUrl: 'http://api', tokens: MemoryTokenStorage(), httpClient: api.client);
    await tester.pumpWidget(IaZestApp(injector: di));

    expect(find.text('Bem-vindo de volta'), findsOneWidget);

    // validação local
    await tester.tap(find.byKey(const Key('login_entrar')));
    await tester.pumpAndSettle();
    expect(find.text('Informe o e-mail.'), findsOneWidget);

    // senha errada
    await tester.enterText(find.byKey(const Key('login_email')), 'demo@iazest.dev');
    await tester.enterText(find.byKey(const Key('login_senha')), 'errada');
    await tester.tap(find.byKey(const Key('login_entrar')));
    await tester.pumpAndSettle();
    expect(find.text('E-mail ou senha incorretos.'), findsOneWidget);

    // senha certa -> lista
    await tester.enterText(find.byKey(const Key('login_senha')), 'Demo@2026');
    await tester.tap(find.byKey(const Key('login_entrar')));
    await tester.pumpAndSettle();
    expect(find.text('Clientes'), findsOneWidget);
    expect(find.text('Padaria Pão Quente Ltda'), findsOneWidget);

    // cadastro
    await tester.tap(find.byKey(const Key('novo_cliente')));
    await tester.pumpAndSettle();
    await tester.enterText(find.byKey(const Key('cad_nome')), 'Maria Souza');
    await tester.enterText(find.byKey(const Key('cad_documento')), '52998224725');
    expect(find.text('529.982.247-25'), findsOneWidget); // máscara aplicada
    await tester.enterText(find.byKey(const Key('cad_email')), 'maria@exemplo.dev');
    await tester.enterText(find.byKey(const Key('cad_telefone')), '31988887777');
    await tester.enterText(find.byKey(const Key('cad_uf')), 'MG');
    await tester.ensureVisible(find.byKey(const Key('cad_salvar')));
    await tester.tap(find.byKey(const Key('cad_salvar')));
    await tester.pumpAndSettle();

    expect(find.text('Cliente Maria Souza cadastrado!'), findsOneWidget);
    expect(find.text('Maria Souza'), findsOneWidget);

    // sair volta para o login
    await tester.tap(find.byTooltip('Sair'));
    await tester.pumpAndSettle();
    expect(find.text('Bem-vindo de volta'), findsOneWidget);
  });
}
