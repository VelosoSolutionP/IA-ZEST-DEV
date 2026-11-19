import '../../../../core/network/api_client.dart';
import '../../domain/entities/cliente.dart';

class ClienteRemoteDataSource {
  const ClienteRemoteDataSource(this._api);
  final ApiClient _api;

  Future<Map<String, dynamic>> criar(NovoCliente n) => _api.post('/api/v1/clientes', {
        'nome': n.nome.trim(),
        'documento': n.documento,
        'email': n.email.trim(),
        'telefone': n.telefone,
        if (n.cidade?.trim().isNotEmpty ?? false) 'cidade': n.cidade!.trim(),
        if (n.uf?.trim().isNotEmpty ?? false) 'uf': n.uf!.trim().toUpperCase(),
      });

  Future<Map<String, dynamic>> listar({String? busca, String? apos}) =>
      _api.get('/api/v1/clientes', query: {'busca': busca, 'apos': apos});
}
