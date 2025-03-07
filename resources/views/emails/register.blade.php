<x-mail::message>
# 🎉 Bienvenue sur **{{ config('app.name') }}** !

Bonjour **{{ $user->firstname }}**,

Merci de vous être inscrit sur **{{ config('app.name') }}**. Avant de commencer à utiliser votre compte, vous devez confirmer votre adresse e-mail en saisissant le code d'activation ci-dessous.

---

## 🔑 Votre code d'activation :
<p style="text-align: center; font-size: 24px; font-weight: bold; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
    {{ $activation_code }}
</p>

---
Ce code est valide **pendant 30 minutes**.


### 📌 Instructions pour activer votre compte :

1. **Ouvrez l’application** **{{ config('app.name') }}**
2. **Accédez à la page de vérification**
3. **Saisissez votre code d'activation**





⚠️ **Si vous n'êtes pas à l'origine de cette inscription, ignorez cet e-mail.**
Si vous avez besoin d'aide, contactez-nous à support@geomatica.space .

Merci de votre confiance,
L’équipe **{{ config('app.name') }}**
</x-mail::message>
