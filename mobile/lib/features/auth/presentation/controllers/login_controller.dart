import 'package:flutter/foundation.dart';

import '../../../../core/network/api_exception.dart';
import '../../domain/entities/usuario.dart';
import '../../domain/usecases/login_usecase.dart';

class LoginController extends ChangeNotifier {
  LoginController(this._login);
  final LoginUseCase _login;

  bool carregando = false;
  String? erro;
  Usuario? usuario;

  Future<bool> entrar(String email, String senha) async {
    if (carregando) return false;
    carregando = true;
    erro = null;
    notifyListeners();
    try {
      usuario = await _login(email: email, senha: senha);
      return true;
    } on DadosLoginInvalidos catch (e) {
      erro = e.mensagem;
    } on ApiException catch (e) {
      erro = e.mensagem;
    } catch (_) {
      erro = 'Não foi possível entrar agora. Tente de novo.';
    } finally {
      carregando = false;
      notifyListeners();
    }
    return false;
  }
}
