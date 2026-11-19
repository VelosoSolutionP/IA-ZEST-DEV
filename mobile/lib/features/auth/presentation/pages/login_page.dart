import 'package:material_ui/material_ui.dart';

import '../../../../core/theme/app_theme.dart';
import '../../../../core/utils/validators.dart';
import '../../domain/entities/usuario.dart';
import '../controllers/login_controller.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key, required this.controller, required this.aoEntrar});

  final LoginController controller;
  final void Function(Usuario usuario) aoEntrar;

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _senha = TextEditingController();
  bool _ocultar = true;

  LoginController get _c => widget.controller;

  @override
  void dispose() {
    _email.dispose();
    _senha.dispose();
    super.dispose();
  }

  Future<void> _entrar() async {
    FocusScope.of(context).unfocus();
    if (!(_form.currentState?.validate() ?? false)) return;
    final ok = await _c.entrar(_email.text, _senha.text);
    if (ok && mounted) widget.aoEntrar(_c.usuario!);
  }

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.gradiente),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: Column(
                  children: [
                    const _Marca(),
                    const SizedBox(height: 28),
                    Card(
                      elevation: 0,
                      color: Colors.white.withValues(alpha: 0.97),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
                      child: Padding(
                        padding: const EdgeInsets.fromLTRB(24, 28, 24, 24),
                        child: Form(
                          key: _form,
                          child: AutofillGroup(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                Text('Bem-vindo de volta', style: tema.textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
                                const SizedBox(height: 6),
                                Text('Entre para gerenciar seus clientes.', style: tema.textTheme.bodyMedium?.copyWith(color: tema.colorScheme.onSurfaceVariant)),
                                const SizedBox(height: 24),
                                TextFormField(
                                  key: const Key('login_email'),
                                  controller: _email,
                                  keyboardType: TextInputType.emailAddress,
                                  textInputAction: TextInputAction.next,
                                  autofillHints: const [AutofillHints.email],
                                  decoration: const InputDecoration(labelText: 'E-mail', prefixIcon: Icon(Icons.alternate_email_rounded)),
                                  validator: Validators.email,
                                ),
                                const SizedBox(height: 14),
                                TextFormField(
                                  key: const Key('login_senha'),
                                  controller: _senha,
                                  obscureText: _ocultar,
                                  textInputAction: TextInputAction.done,
                                  autofillHints: const [AutofillHints.password],
                                  onFieldSubmitted: (_) => _entrar(),
                                  decoration: InputDecoration(
                                    labelText: 'Senha',
                                    prefixIcon: const Icon(Icons.lock_outline_rounded),
                                    suffixIcon: IconButton(
                                      tooltip: _ocultar ? 'Mostrar senha' : 'Ocultar senha',
                                      icon: Icon(_ocultar ? Icons.visibility_outlined : Icons.visibility_off_outlined),
                                      onPressed: () => setState(() => _ocultar = !_ocultar),
                                    ),
                                  ),
                                  validator: Validators.senha,
                                ),
                                const SizedBox(height: 20),
                                ListenableBuilder(
                                  listenable: _c,
                                  builder: (context, _) => Column(
                                    crossAxisAlignment: CrossAxisAlignment.stretch,
                                    children: [
                                      if (_c.erro != null) _AvisoErro(_c.erro!),
                                      FilledButton(
                                        key: const Key('login_entrar'),
                                        onPressed: _c.carregando ? null : _entrar,
                                        child: _c.carregando
                                            ? const SizedBox.square(dimension: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                                            : const Text('Entrar'),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),
                    Text('Conexão protegida · JWT + bcrypt', style: tema.textTheme.bodySmall?.copyWith(color: Colors.white70)),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _Marca extends StatelessWidget {
  const _Marca();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          width: 76,
          height: 76,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(24),
            gradient: const LinearGradient(colors: [AppTheme.roxo, AppTheme.azul]),
            boxShadow: [BoxShadow(color: AppTheme.azul.withValues(alpha: 0.45), blurRadius: 28, offset: const Offset(0, 10))],
          ),
          child: const Icon(Icons.bolt_rounded, color: Colors.white, size: 44),
        ),
        const SizedBox(height: 16),
        const Text('IA-ZEST', style: TextStyle(color: Colors.white, fontSize: 30, fontWeight: FontWeight.w800, letterSpacing: 1.5)),
        const Text('Clientes na palma da mão', style: TextStyle(color: Colors.white70, fontSize: 15)),
      ],
    );
  }
}

class _AvisoErro extends StatelessWidget {
  const _AvisoErro(this.texto);
  final String texto;

  @override
  Widget build(BuildContext context) {
    final cores = Theme.of(context).colorScheme;
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: cores.errorContainer, borderRadius: BorderRadius.circular(12)),
      child: Row(
        children: [
          Icon(Icons.error_outline_rounded, color: cores.onErrorContainer),
          const SizedBox(width: 10),
          Expanded(child: Text(texto, style: TextStyle(color: cores.onErrorContainer))),
        ],
      ),
    );
  }
}
