import 'package:http/http.dart' as http;

import '../../features/auth/data/datasources/auth_remote_datasource.dart';
import '../../features/auth/data/repositories/auth_repository_impl.dart';
import '../../features/auth/domain/repositories/auth_repository.dart';
import '../../features/auth/domain/usecases/login_usecase.dart';
import '../../features/auth/presentation/controllers/login_controller.dart';
import '../../features/clientes/data/datasources/cliente_remote_datasource.dart';
import '../../features/clientes/data/repositories/cliente_repository_impl.dart';
import '../../features/clientes/domain/repositories/cliente_repository.dart';
import '../../features/clientes/domain/usecases/cadastrar_cliente_usecase.dart';
import '../../features/clientes/domain/usecases/listar_clientes_usecase.dart';
import '../../features/clientes/presentation/controllers/cadastro_cliente_controller.dart';
import '../../features/clientes/presentation/controllers/clientes_controller.dart';
import '../config/env.dart';
import '../network/api_client.dart';
import '../storage/token_storage.dart';

/// Composição das dependências (sem service locator global: tudo explícito e testável).
class Injector {
  Injector({String? apiUrl, TokenStorage? tokens, http.Client? httpClient})
      : tokens = tokens ?? SecureTokenStorage() {
    api = ApiClient(baseUrl: apiUrl ?? Env.apiUrl, tokens: this.tokens, client: httpClient);
    auth = AuthRepositoryImpl(AuthRemoteDataSource(api), this.tokens);
    clientes = ClienteRepositoryImpl(ClienteRemoteDataSource(api));
  }

  final TokenStorage tokens;
  late final ApiClient api;
  late final AuthRepository auth;
  late final ClienteRepository clientes;

  LoginController loginController() => LoginController(LoginUseCase(auth));
  ClientesController clientesController() => ClientesController(ListarClientesUseCase(clientes));
  CadastroClienteController cadastroController() => CadastroClienteController(CadastrarClienteUseCase(clientes));
}
