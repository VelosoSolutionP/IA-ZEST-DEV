import 'dart:async';

import 'package:flutter/foundation.dart';

import '../../../../core/network/api_exception.dart';
import '../../domain/entities/cliente.dart';
import '../../domain/usecases/listar_clientes_usecase.dart';

class ClientesController extends ChangeNotifier {
  ClientesController(this._listar);
  final ListarClientesUseCase _listar;

  final List<Cliente> itens = [];
  String? _proximo;
  String _busca = '';
  bool carregando = false;
  String? erro;
  Timer? _espera;
  int _geracao = 0;

  bool get temMais => _proximo != null;

  Future<void> recarregar() => _carregar(reiniciar: true);

  Future<void> carregarMais() async {
    if (temMais && !carregando) await _carregar(reiniciar: false);
  }

  /// Busca com "debounce": não chama a API a cada tecla.
  void buscar(String texto, {Duration atraso = const Duration(milliseconds: 350)}) {
    _busca = texto;
    _espera?.cancel();
    _espera = Timer(atraso, recarregar);
  }

  void adicionarNoTopo(Cliente c) {
    itens.insert(0, c);
    notifyListeners();
  }

  Future<void> _carregar({required bool reiniciar}) async {
    final geracao = reiniciar ? ++_geracao : _geracao;
    carregando = true;
    erro = null;
    notifyListeners();
    try {
      final p = await _listar(busca: _busca, apos: reiniciar ? null : _proximo);
      if (geracao != _geracao) return; // resposta velha de uma busca anterior
      if (reiniciar) itens.clear();
      itens.addAll(p.itens);
      _proximo = p.proximo;
    } on ApiException catch (e) {
      erro = e.mensagem;
    } finally {
      if (geracao == _geracao) {
        carregando = false;
        notifyListeners();
      }
    }
  }

  @override
  void dispose() {
    _espera?.cancel();
    super.dispose();
  }
}
