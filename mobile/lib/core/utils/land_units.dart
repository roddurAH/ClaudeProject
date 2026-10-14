/// Local land units. Farmers rarely think in hectares, so the calculator
/// accepts the units they actually use.
enum LandUnit { decimal, katha, bigha, acre, hectare }

class LandUnits {
  LandUnits._();

  static const double decimalsPerHectare = 247.105;
  static const double decimalsPerAcre = 100;

  /// [bighaDecimal] – how many decimals make one bigha in the user's area
  /// (33 is the common standard; 20 katha = 1 bigha).
  static double toDecimals(double value, LandUnit unit, {double bighaDecimal = 33}) {
    return switch (unit) {
      LandUnit.decimal => value,
      LandUnit.katha => value * bighaDecimal / 20,
      LandUnit.bigha => value * bighaDecimal,
      LandUnit.acre => value * decimalsPerAcre,
      LandUnit.hectare => value * decimalsPerHectare,
    };
  }

  static double toHectares(double value, LandUnit unit, {double bighaDecimal = 33}) =>
      toDecimals(value, unit, bighaDecimal: bighaDecimal) / decimalsPerHectare;
}
