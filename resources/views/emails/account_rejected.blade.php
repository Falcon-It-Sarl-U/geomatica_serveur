<x-mail::message>
# ❌ Votre compte a été refusé, {{ $user->firstname }}.

## **Motif du refus :**
> *"{{ $motif }}"*

Nous comprenons que cela puisse être décevant.
Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez plus d’informations, n’hésitez pas à nous contacter.

<x-mail::button :url="config('app.url').'/contact'">
📩 Contacter l’assistance
</x-mail::button>

Nous restons à votre disposition pour toute clarification.

Merci pour votre compréhension,
**L'équipe {{ $app_name }}**
</x-mail::message>
