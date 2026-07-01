import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/providers/auth_provider.dart';
import 'package:portal_warga/providers/dashboard_provider.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(dashboardProvider.notifier).fetchWargaDashboard());
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final dashState = ref.watch(dashboardProvider);
    final theme = Theme.of(context);

    return RefreshIndicator(
      onRefresh: () => ref.read(dashboardProvider.notifier).fetchWargaDashboard(),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text('Halo, ${authState.user?.name ?? 'Warga'}!', style: theme.textTheme.titleLarge),
          const SizedBox(height: 16),

          if (dashState.isLoading)
            const Center(child: Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator()))
          else if (dashState.data != null) ...[
            _InfoCards(dashState.data!),
            const SizedBox(height: 16),
            _QuickActions(theme),
            const SizedBox(height: 16),
            _BeritaTerbaru(dashState.data!['berita_terbaru'] ?? [], context),
            const SizedBox(height: 16),
            _AgendaTerdekat(dashState.data!['agenda_terdekat'] ?? [], context),
          ],
        ],
      ),
    );
  }
}

class _InfoCards extends StatelessWidget {
  final Map<String, dynamic> data;
  const _InfoCards(this.data);

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(
          children: [
            _Card('Pengaduan Aktif', '${data['pengaduan_aktif'] ?? 0}', Icons.report_problem, Colors.orange),
            const SizedBox(width: 12),
            _Card('Surat Pending', '${data['surat_pending'] ?? 0}', Icons.description, Colors.blue),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            _Card('Iuran Bulan Ini', data['iuran_bulan_ini']?['status'] ?? 'belum_bayar', Icons.account_balance_wallet, Colors.green),
          ],
        ),
      ],
    );
  }
}

class _Card extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const _Card(this.title, this.value, this.icon, this.color);

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [Icon(icon, color: color, size: 20), const SizedBox(width: 8), Text(title, style: const TextStyle(fontSize: 12, color: Colors.grey))]),
              const SizedBox(height: 8),
              Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
            ],
          ),
        ),
      ),
    );
  }
}

class _QuickActions extends StatelessWidget {
  final ThemeData theme;
  const _QuickActions(this.theme);

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Aksi Cepat', style: theme.textTheme.titleMedium),
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(child: _ActionButton('Buat Pengaduan', Icons.edit_note, () => Navigator.pushNamed(context, '/pengaduan/buat'))),
            const SizedBox(width: 8),
            Expanded(child: _ActionButton('Ajukan Surat', Icons.note_add, () => Navigator.pushNamed(context, '/surat/ajukan'))),
            const SizedBox(width: 8),
            Expanded(child: _ActionButton('Bayar Iuran', Icons.payment, () => Navigator.pushNamed(context, '/iuran/bayar'))),
          ],
        ),
      ],
    );
  }
}

class _ActionButton extends StatelessWidget {
  final String label;
  final IconData icon;
  final VoidCallback onTap;

  const _ActionButton(this.label, this.icon, this.onTap);

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: onTap,
      style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 12)),
      child: Column(children: [Icon(icon, size: 24), const SizedBox(height: 4), Text(label, style: const TextStyle(fontSize: 11))]),
    );
  }
}

class _BeritaTerbaru extends StatelessWidget {
  final List berita;
  final BuildContext parentContext;
  const _BeritaTerbaru(this.berita, this.parentContext);

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Berita Terbaru', style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 8),
        ...berita.map((b) => Card(
          child: ListTile(
            title: Text(b['judul'] ?? '', maxLines: 1, overflow: TextOverflow.ellipsis),
            subtitle: Text(b['kategori'] ?? '', style: const TextStyle(fontSize: 12)),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => Navigator.pushNamed(parentContext, '/berita/${b['id']}'),
          ),
        )),
      ],
    );
  }
}

class _AgendaTerdekat extends StatelessWidget {
  final List agenda;
  final BuildContext parentContext;
  const _AgendaTerdekat(this.agenda, this.parentContext);

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Agenda Terdekat', style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 8),
        ...agenda.map((a) => Card(
          child: ListTile(
            leading: const Icon(Icons.event),
            title: Text(a['nama'] ?? '', maxLines: 1, overflow: TextOverflow.ellipsis),
            subtitle: Text('${a['tanggal'] ?? ''} ${a['jam'] ?? ''}'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => Navigator.pushNamed(parentContext, '/agenda/${a['id']}'),
          ),
        )),
        if (agenda.isEmpty) const Text('Belum ada agenda terdekat', style: TextStyle(color: Colors.grey)),
      ],
    );
  }
}
