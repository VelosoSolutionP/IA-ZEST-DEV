import 'package:material_ui/material_ui.dart';

import '../../../auth/domain/entities/usuario.dart';
import '../../domain/entities/cliente.dart';
import '../controllers/clientes_controller.dart';
import '../widgets/cliente_tile.dart';

class ClientesPage extends StatefulWidget {
  const ClientesPage({
    super.key,
    required this.usuario,
    required this.controller,
    required this.abrirCadastro,
    required this.aoSair,
  });

  final Usuario usuario;
  final ClientesController controller;
  final Future<Cliente?> Function(BuildContext context) abrirCadastro;
  final VoidCallback aoSair;

  @override
  State<ClientesPage> createState() => _ClientesPageState();
}

class _ClientesPageState extends State<ClientesPage> {
  final _rolagem = ScrollController();

  ClientesController get _c => widget.controller;

  @override
  void initState() {
    super.initState();
    _c.recarregar();
    _rolagem.addListener(() {
      if (_rolagem.position.extentAfter < 300) _c.carregarMais();
    });
  }

  @override
  void dispose() {
    _rolagem.dispose();
    super.dispose();
  }

  Future<void> _novo() async {
    final criado = await widget.abrirCadastro(context);
    if (criado == null || !mounted) return;
    _c.adicionarNoTopo(criado);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Cliente ${criado.nome} cadastrado!'), behavior: SnackBarBehavior.floating),
    );
  }

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Clientes', style: TextStyle(fontWeight: FontWeight.w700)),
            Text('Olá, ${widget.usuario.primeiroNome}', style: tema.textTheme.bodySmall),
          ],
        ),
        actions: [
          IconButton(tooltip: 'Sair', icon: const Icon(Icons.logout_rounded), onPressed: widget.aoSair),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        key: const Key('novo_cliente'),
        onPressed: _novo,
        icon: const Icon(Icons.person_add_alt_1_rounded),
        label: const Text('Novo cliente'),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
            child: TextField(
              key: const Key('buscar_cliente'),
              onChanged: _c.buscar,
              decoration: const InputDecoration(labelText: 'Buscar cliente', prefixIcon: Icon(Icons.search_rounded)),
            ),
          ),
          Expanded(
            child: ListenableBuilder(
              listenable: _c,
              builder: (context, _) {
                if (_c.erro != null && _c.itens.isEmpty) {
                  return _Vazio(icone: Icons.cloud_off_rounded, texto: _c.erro!, acao: _c.recarregar);
                }
                if (_c.itens.isEmpty) {
                  return _c.carregando
                      ? const Center(child: CircularProgressIndicator())
                      : const _Vazio(icone: Icons.people_outline_rounded, texto: 'Nenhum cliente ainda.');
                }
                return RefreshIndicator(
                  onRefresh: _c.recarregar,
                  child: ListView.builder(
                    controller: _rolagem,
                    padding: const EdgeInsets.only(bottom: 96),
                    itemCount: _c.itens.length + (_c.temMais ? 1 : 0),
                    itemBuilder: (context, i) => i < _c.itens.length
                        ? ClienteTile(_c.itens[i], key: ValueKey(_c.itens[i].id))
                        : const Padding(padding: EdgeInsets.all(16), child: Center(child: CircularProgressIndicator())),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _Vazio extends StatelessWidget {
  const _Vazio({required this.icone, required this.texto, this.acao});
  final IconData icone;
  final String texto;
  final Future<void> Function()? acao;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icone, size: 56, color: Theme.of(context).colorScheme.outline),
          const SizedBox(height: 12),
          Text(texto, textAlign: TextAlign.center),
          if (acao != null) TextButton(onPressed: acao, child: const Text('Tentar de novo')),
        ],
      ),
    );
  }
}
