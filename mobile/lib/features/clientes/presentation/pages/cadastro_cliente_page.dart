import 'package:flutter/services.dart';
import 'package:material_ui/material_ui.dart';

import '../../../../core/utils/masks.dart';
import '../../../../core/utils/validators.dart';
import '../../domain/entities/cliente.dart';
import '../controllers/cadastro_cliente_controller.dart';

class CadastroClientePage extends StatefulWidget {
  const CadastroClientePage({super.key, required this.controller});
  final CadastroClienteController controller;

  @override
  State<CadastroClientePage> createState() => _CadastroClientePageState();
}

class _CadastroClientePageState extends State<CadastroClientePage> {
  final _form = GlobalKey<FormState>();
  final _nome = TextEditingController();
  final _documento = TextEditingController();
  final _email = TextEditingController();
  final _telefone = TextEditingController();
  final _cidade = TextEditingController();
  final _uf = TextEditingController();

  CadastroClienteController get _c => widget.controller;

  @override
  void dispose() {
    for (final c in [_nome, _documento, _email, _telefone, _cidade, _uf]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _salvar() async {
    FocusScope.of(context).unfocus();
    if (!(_form.currentState?.validate() ?? false)) return;
    final cliente = await _c.salvar(NovoCliente(
      nome: _nome.text,
      documento: _documento.text,
      email: _email.text,
      telefone: _telefone.text,
      cidade: _cidade.text,
      uf: _uf.text,
    ));
    if (!mounted) return;
    if (cliente != null) {
      Navigator.of(context).pop(cliente);
    } else {
      _form.currentState?.validate(); // mostra os erros que vieram da API embaixo de cada campo
    }
  }

  String? Function(String?) _comApi(String campo, String? Function(String?) local) =>
      (v) => local(v) ?? _c.errosCampos[campo];

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: const Text('Novo cliente')),
      body: SafeArea(
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 560),
            child: Form(
              key: _form,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 32),
                children: [
                  Text('Dados do cliente', style: tema.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 4),
                  Text('CPF para pessoa física, CNPJ para empresa. A máscara se ajusta sozinha.',
                      style: tema.textTheme.bodySmall?.copyWith(color: tema.colorScheme.onSurfaceVariant)),
                  const SizedBox(height: 18),
                  _campo(
                    key: 'cad_nome',
                    controller: _nome,
                    rotulo: 'Nome ou razão social',
                    icone: Icons.person_outline_rounded,
                    validator: _comApi('nome', Validators.nome),
                    capitalizacao: TextCapitalization.words,
                  ),
                  _campo(
                    key: 'cad_documento',
                    controller: _documento,
                    rotulo: 'CPF ou CNPJ',
                    icone: Icons.badge_outlined,
                    teclado: TextInputType.number,
                    mascaras: [DocumentoMask()],
                    validator: _comApi('documento', Validators.documento),
                  ),
                  _campo(
                    key: 'cad_email',
                    controller: _email,
                    rotulo: 'E-mail',
                    icone: Icons.alternate_email_rounded,
                    teclado: TextInputType.emailAddress,
                    validator: _comApi('email', Validators.email),
                  ),
                  _campo(
                    key: 'cad_telefone',
                    controller: _telefone,
                    rotulo: 'Telefone',
                    icone: Icons.phone_outlined,
                    teclado: TextInputType.phone,
                    mascaras: [TelefoneMask()],
                    validator: _comApi('telefone', Validators.telefone),
                  ),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        flex: 3,
                        child: _campo(
                          key: 'cad_cidade',
                          controller: _cidade,
                          rotulo: 'Cidade',
                          icone: Icons.location_city_outlined,
                          capitalizacao: TextCapitalization.words,
                          validator: (v) => _c.errosCampos['cidade'],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _campo(
                          key: 'cad_uf',
                          controller: _uf,
                          rotulo: 'UF',
                          capitalizacao: TextCapitalization.characters,
                          mascaras: [LengthLimitingTextInputFormatter(2), FilteringTextInputFormatter.allow(RegExp('[a-zA-Z]'))],
                          validator: _comApi('uf', Validators.uf),
                          acao: TextInputAction.done,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  ListenableBuilder(
                    listenable: _c,
                    builder: (context, _) => Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        if (_c.erro != null)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child: Text(_c.erro!, style: TextStyle(color: tema.colorScheme.error, fontWeight: FontWeight.w600)),
                          ),
                        FilledButton.icon(
                          key: const Key('cad_salvar'),
                          onPressed: _c.salvando ? null : _salvar,
                          icon: _c.salvando
                              ? const SizedBox.square(dimension: 20, child: CircularProgressIndicator(strokeWidth: 2.5))
                              : const Icon(Icons.check_rounded),
                          label: const Text('Salvar cliente'),
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
    );
  }

  Widget _campo({
    required String key,
    required TextEditingController controller,
    required String rotulo,
    IconData? icone,
    TextInputType? teclado,
    List<TextInputFormatter>? mascaras,
    String? Function(String?)? validator,
    TextCapitalization capitalizacao = TextCapitalization.none,
    TextInputAction acao = TextInputAction.next,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: TextFormField(
        key: Key(key),
        controller: controller,
        keyboardType: teclado,
        inputFormatters: mascaras,
        textCapitalization: capitalizacao,
        textInputAction: acao,
        validator: validator,
        decoration: InputDecoration(labelText: rotulo, prefixIcon: icone == null ? null : Icon(icone)),
      ),
    );
  }
}
