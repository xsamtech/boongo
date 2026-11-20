# Paiement direct d’une consultation ou d’un abonnement

- `POST /api/work/purchase/{work_id}` : montant issu de `works.consultation_price`, devise issue de sa relation `currency`.
- `POST /api/subscription/purchase/{subscription_id}` : montant issu de `subscriptions.price`, devise issue de sa relation `currency`.

Les deux routes exigent une authentification Sanctum. Le payeur est l’utilisateur authentifié. Les champs `amount`, `currency` ou `user_id` envoyés par un client ne remplacent jamais le prix, la devise ou le payeur déterminés par le serveur.

Corps JSON pour Mobile money :

```json
{
  "transaction_type_id": 11,
  "other_phone": "+243812345678",
  "channel": "mobile"
}
```

`11` est un exemple : utiliser l’identifiant réel du type **Mobile money** appartenant au groupe **Type de paiement**. Le numéro international est obligatoire pour ce mode.

Pour la carte bancaire, utiliser l’identifiant du type **Carte bancaire** et fournir `app_url`, une URL HTTP(S) de retour. La réponse contient alors `data.result_response.url`, l’adresse de la page de paiement FlexPay. Les URL de retour reprennent le chemin `/subscribed/{montant}/USD/{résultat}/{user_id}` du circuit existant. Aucun jeton d’API utilisateur n’est ajouté à ces URL.

## Conversion vers USD

Si `currency_acronym` vaut déjà USD, le prix est utilisé tel quel. Sinon :

1. Chercher le taux le plus récent de la devise source vers USD dans `currencies_rates` ; multiplier le prix par ce taux.
2. Si ce sens n’existe pas, chercher USD vers la devise source ; diviser le prix par ce taux.
3. Arrondir le résultat au centime, au plus proche et vers le haut en cas de demi-centime, avec des calculs en entiers. Les montants inférieurs à un centime après arrondi sont refusés.

Le taux le plus récent est sélectionné par `created_at` décroissant, puis `id` décroissant. Un taux direct, même plus ancien, a priorité sur le sens inverse. Il n’y a pas de conversion par une devise intermédiaire.

Exemple : `25 000 CDF × 0,00035 = 8,75 USD`. La requête FlexPay et l’enregistrement dans `payments` utilisent tous deux `8.75` et `USD`. Les prix d’origine des œuvres et abonnements restent inchangés. Une devise absente, un taux absent ou non positif, ou un montant incompatible avec `payments.amount` empêche l’appel à FlexPay.

## Suivi du paiement

Le service utilise le même contrat `services.flexpay.gateway_mobile` que `CartController::purchase`, avec `type = 1` pour Mobile money et `type = 2` pour carte bancaire, ainsi que le callback existant `/api/payment/store`.

Après acceptation de la demande par FlexPay, un paiement **En cours** et un panier dédié à cet élément sont enregistrés. Le panier reprend le statut **Payé** du circuit existant, mais la consultation ou l’abonnement attaché reste **En attente**. L’accès ne devient pas valide à cette étape : la confirmation du paiement et les méthodes existantes de validation restent nécessaires. Un callback arrivé avant la réponse HTTP ne voit pas son statut réinitialisé.

Le panier dédié ne modifie pas les autres articles du panier en cours. La réponse conserve l’enveloppe habituelle `success`, `message`, `data`, `ad`. Dans `data` figurent notamment `payment_id`, `cart_id`, `entity`, `entity_id`, `amount`, `currency`, `reference` et `result_response` (`message`, `order_number`, `url`).

Une nouvelle requête initie une nouvelle tentative de paiement : après un délai d’attente FlexPay, vérifier le résultat de la première tentative avant de réessayer. Aucun nouvel essai automatique de l’appel fournisseur n’est effectué.

Les champs et les réponses HTTP 401, 404, 422, 502 et 503 sont décrits dans Scramble.
