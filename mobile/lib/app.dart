import 'package:material_ui/material_ui.dart';

import 'core/di/injector.dart';
import 'core/theme/app_theme.dart';
import 'features/auth/domain/entities/usuario.dart';
import 'features/auth/presentation/pages/login_page.dart';
import 'features/clientes/domain/entities/cliente.dart';
import 'features/clientes/presentation/pages/cadastro_cliente_page.dart';
import 'features/clientes/presentation/pages/clientes_page.dart';

class IaZestApp extends StatefulWidget {
  const IaZestApp({super.key, required this.injector, this.usuarioInicial});
  final Injector injector;
  final Usuario? usuarioInicial;

  @override
  State<IaZestApp> createState() => _IaZestAppState();
}

class _IaZestAppState extends State<IaZestApp> {
  final _navegador = GlobalKey<NavigatorState>();
  late Usuario? _usuario = widget.usuarioInicial;

  Injector get _di => widget.injector;

  @override
  void initState() {
    super.initState();
    _di.api.aoExpirarSessao = _sair; // token vencido em qualquer tela -> volta ao login
  }

  void _entrou(Usuario u) => setState(() => _usuario = u);

  Future<void> _sair() async {
    await _di.auth.sair();
    if (!mounted) return;
    _navegador.currentState?.popUntil((r) => r.isFirst);
    setState(() => _usuario = null);
  }

  Future<Cliente?> _abrirCadastro(BuildContext context) {
    return Navigator.of(context).push<Cliente>(
      MaterialPageRoute(builder: (_) => CadastroClientePage(controller: _di.cadastroController())),
    );
  }

  @override
  Widget build(BuildContext context) {
    final usuario = _usuario;
    return MaterialApp(
      title: 'IA-ZEST',
      debugShowCheckedModeBanner: false,
      navigatorKey: _navegador,
      theme: AppTheme.claro(),
      home: AnimatedSwitcher(
        duration: const Duration(milliseconds: 350),
        child: usuario == null
            ? LoginPage(key: const ValueKey('login'), controller: _di.loginController(), aoEntrar: _entrou)
            : ClientesPage(
                key: ValueKey(usuario.id),
                usuario: usuario,
                controller: _di.clientesController(),
                abrirCadastro: _abrirCadastro,
                aoSair: _sair,
              ),
      ),
    );
  }
}
