import 'package:material_ui/material_ui.dart';

abstract final class AppTheme {
  static const roxo = Color(0xFF6C4CF1);
  static const azul = Color(0xFF2E8BFF);
  static const fundoEscuro = Color(0xFF0E0B1F);

  static const gradiente = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFF1B1140), Color(0xFF3A1F8F), Color(0xFF1D4ED8)],
  );

  static ThemeData claro() {
    final cores = ColorScheme.fromSeed(seedColor: roxo, brightness: Brightness.light);
    return ThemeData(
      useMaterial3: true,
      colorScheme: cores,
      scaffoldBackgroundColor: const Color(0xFFF6F5FB),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: cores.outlineVariant),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size.fromHeight(54),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
      appBarTheme: const AppBarTheme(centerTitle: false, scrolledUnderElevation: 0),
    );
  }
}
