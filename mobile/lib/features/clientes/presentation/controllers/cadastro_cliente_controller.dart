import 'package:flutter/foundation.dart';

import '../../../../core/network/api_exception.dart';
import '../../domain/entities/cliente.dart';
import '../../domain/usecases/cadastrar_cliente_usecase.dart';

class CadastroClienteController extends ChangeNotifier {
  CadastroClienteController(this._cadastrar);
  final CadastrarClienteUseCase _cadastrar;

  bool salvando = false;
  String? erro;
  Map<String, String> errosCampos = const {};

  Future<Cliente?> salvar(NovoCliente novo) async {
    if (salvando) return null;
    salvando = true;
    erro = null;
    errosCampos = const {};
    notifyListeners();
    try {
      return await _cadastrar(novo);
    } on ClienteInvalido catch (e) {
      errosCampos = e.campos;
    } on ApiException catch (e) {
      erro = e.mensagem;
      errosCampos = e.campos;
    } finally {
      salvando = false;
      notifyListeners();
    }
    return null;
  }
}
