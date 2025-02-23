<x-mail::message>
# 🎉 Félicitations, {{ $user->firstname }} ! 🎊

Votre compte sur **{{ $app_name }}** a été activé avec succès. Vous pouvez maintenant vous connecter et profiter de toutes nos fonctionnalités.

<x-mail::button :url="$login_url">
🚀 Se connecter
</x-mail::button>

Si vous avez des questions, n’hésitez pas à nous contacter.

Merci pour votre confiance,
**L'équipe {{ $app_name }}**
</x-mail::message>
