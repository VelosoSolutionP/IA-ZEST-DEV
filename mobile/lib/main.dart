import 'package:material_ui/material_ui.dart';

import 'app.dart';
import 'core/di/injector.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final injector = Injector();
  final usuario = await injector.auth.sessaoAtual(); // já logado? pula a tela de login
  runApp(IaZestApp(injector: injector, usuarioInicial: usuario));
}
