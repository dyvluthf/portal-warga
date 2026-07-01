import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:portal_warga/providers/agenda_provider.dart';

class DetailAgendaScreen extends ConsumerStatefulWidget {
  final int id;
  final String nama;
  final String tanggal;
  final String jam;
  final String lokasi;
  final String? deskripsi;

  const DetailAgendaScreen({
    super.key,
    required this.id,
    required this.nama,
    required this.tanggal,
    required this.jam,
    required this.lokasi,
    this.deskripsi,
  });

  @override
  ConsumerState<DetailAgendaScreen> createState() => _DetailAgendaScreenState();
}

class _DetailAgendaScreenState extends ConsumerState<DetailAgendaScreen> {
  String? _rsvpStatus;
  bool _loading = false;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Detail Agenda')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(widget.nama, style: theme.textTheme.titleLarge),
                  const SizedBox(height: 12),
                  _infoRow(Icons.calendar_today, '${widget.tanggal} ${widget.jam}'),
                  const SizedBox(height: 8),
                  _infoRow(Icons.location_on, widget.lokasi),
                  if (widget.deskripsi != null) ...[
                    const SizedBox(height: 12),
                    Text(widget.deskripsi!, style: const TextStyle(color: Colors.grey)),
                  ],
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    icon: const Icon(Icons.map),
                    label: const Text('Buka di Google Maps'),
                    onPressed: () async {
                      final uri = Uri.parse('https://www.google.com/maps/search/${Uri.encodeComponent(widget.lokasi)}');
                      if (await canLaunchUrl(uri)) await launchUrl(uri);
                    },
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text('RSVP', style: theme.textTheme.titleMedium),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.check),
                  label: const Text('Hadir'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _rsvpStatus == 'hadir' ? Colors.green : null,
                  ),
                  onPressed: _loading ? null : () => _doRsvp('hadir'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.close),
                  label: const Text('Tidak Hadir'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _rsvpStatus == 'tidak_hadir' ? Colors.red : null,
                  ),
                  onPressed: _loading ? null : () => _doRsvp('tidak_hadir'),
                ),
              ),
            ],
          ),
          if (_rsvpStatus != null)
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: Text('Kamu: ${_rsvpStatus == 'hadir' ? '✅ Hadir' : '❌ Tidak Hadir'}',
                style: TextStyle(color: _rsvpStatus == 'hadir' ? Colors.green : Colors.red)),
            ),
        ],
      ),
    );
  }

  Widget _infoRow(IconData icon, String text) {
    return Row(children: [
      Icon(icon, size: 18, color: Colors.grey),
      const SizedBox(width: 8),
      Expanded(child: Text(text)),
    ]);
  }

  Future<void> _doRsvp(String status) async {
    setState(() => _loading = true);
    final msg = await ref.read(agendaProvider.notifier).rsvp(widget.id, status);
    if (!mounted) return;
    setState(() { _loading = false; _rsvpStatus = msg == null ? status : null; });
    if (msg != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
    }
  }
}
