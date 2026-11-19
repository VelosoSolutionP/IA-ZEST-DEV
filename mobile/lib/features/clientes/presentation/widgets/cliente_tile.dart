import 'package:material_ui/material_ui.dart';

import '../../domain/entities/cliente.dart';

class ClienteTile extends StatelessWidget {
  const ClienteTile(this.cliente, {super.key});
  final Cliente cliente;

  @override
  Widget build(BuildContext context) {
    final cores = Theme.of(context).colorScheme;
    final pj = cliente.tipoPessoa == 'PJ';
    return Card(
      elevation: 0,
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18), side: BorderSide(color: cores.outlineVariant)),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: CircleAvatar(
          backgroundColor: pj ? cores.primaryContainer : cores.tertiaryContainer,
          foregroundColor: pj ? cores.onPrimaryContainer : cores.onTertiaryContainer,
          child: Text(cliente.iniciais, style: const TextStyle(fontWeight: FontWeight.w700)),
        ),
        title: Text(cliente.nome, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text([cliente.documento, ?cliente.local].join(' · ')),
        trailing: Chip(
          label: Text(cliente.tipoPessoa),
          visualDensity: VisualDensity.compact,
          side: BorderSide.none,
          backgroundColor: pj ? cores.primaryContainer : cores.tertiaryContainer,
        ),
      ),
    );
  }
}
