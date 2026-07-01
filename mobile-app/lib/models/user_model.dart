class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;
  final bool statusAktif;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.statusAktif,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'],
      name: json['name'],
      email: json['email'] ?? '',
      role: json['role'] ?? 'warga',
      statusAktif: json['status_aktif'] ?? true,
    );
  }
}
