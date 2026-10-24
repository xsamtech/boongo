# Regroupement et ordre des fichiers dans les APIs

Les listes génériques de fichiers (`GET /api/file`, réponse de suppression d’un fichier, et `Program.files`) sont regroupées par `files.type_id`. Les clés des groupes sont les identifiants des types ; la clé `untyped` correspond à `type_id = null`.

Exemple de `data` pour l’API fichiers :

```json
{
  "12": [
    { "id": 8, "type_id": 12, "position": 0 },
    { "id": 4, "type_id": 12, "position": 2 },
    { "id": 9, "type_id": 12, "position": null }
  ],
  "18": [
    { "id": 5, "type_id": 18, "position": 1 }
  ]
}
```

Les autres champs de chaque fichier restent présents. Les listes déjà séparées par type dans les œuvres et messages (`images`, `documents`, `audios`) gardent leurs noms.

Dans chaque groupe, les positions renseignées sont triées par ordre numérique croissant, y compris zéro. Les positions nulles viennent ensuite. En cas d’égalité, les fichiers sont triés par `id` croissant, pour rendre l’ordre stable. Les fichiers représentatifs d’une œuvre (`photo_url`, `video_url`, `document_url`, `audio_url`) suivent ce même ordre.

La pagination de l’API fichiers reste calculée sur les fichiers : le tri intervient avant le découpage en pages, puis chaque page est regroupée par type. Un type peut donc apparaître sur plusieurs pages. `count` reste le nombre total de fichiers et `lastPage` reste le nombre total de pages. Une liste vide reste `[]`.

Scramble décrit la forme des groupes et les ressources `File` qu’ils contiennent. Cette forme remplace le tableau plat des listes génériques ; les clients qui les parcouraient doivent désormais parcourir chaque groupe.
