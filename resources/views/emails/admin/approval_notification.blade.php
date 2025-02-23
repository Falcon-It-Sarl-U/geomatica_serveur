@component('mail::message')
# 🔔 Nouvelle Activation en Attente

Un nouvel utilisateur vient d’activer son compte et attend une validation de votre part.

### 👤 Informations de l’utilisateur :
- **Nom :** {{ $user->firstname }} {{ $user->lastname }}
- **Email :** {{ $user->email }}
- **Entreprise :** {{ $user->company_name ?? 'Non spécifiée' }}

@component('mail::button', ['url' => $admin_url])
📌 Valider ou Refuser
@endcomponent

Merci de traiter cette demande dès que possible.

Cordialement,
**L’équipe {{ config('app.name') }}**
@endcomponent
