import '../../models/content.dart';

class FertilizerAmount {
  FertilizerAmount({required this.fertilizer, required this.kg, required this.dose});

  final Fertilizer fertilizer;
  final double kg;
  final FertilizerDose dose;

  /// Cost at the government price, or null when no official price exists.
  double? get cost => fertilizer.officialPricePerKg == null ? null : kg * fertilizer.officialPricePerKg!;
}

/// Converts per-hectare recommendations into amounts for the user's land.
class FertilizerCalculator {
  FertilizerCalculator._();

  static List<FertilizerAmount> calculate(Crop crop, KnowledgeBase kb, double hectares) {
    final result = <FertilizerAmount>[];
    for (final dose in crop.doses) {
      final fertilizer = kb.fertilizerById(dose.fertilizerId);
      if (fertilizer == null) continue;
      result.add(FertilizerAmount(fertilizer: fertilizer, kg: dose.kgPerHectare * hectares, dose: dose));
    }
    return result;
  }

  static double totalCost(List<FertilizerAmount> amounts) =>
      amounts.fold(0, (sum, a) => sum + (a.cost ?? 0));
}
