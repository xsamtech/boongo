# Fichiers et couvertures des œuvres

`POST /api/work` et `POST /api/work/upload_files` acceptent des fichiers en `multipart/form-data` :

| Champ | Destination | Usage |
| --- | --- | --- |
| `files_urls[index]` | `files.file_url` | Fichier principal : photo, vidéo, document ou audio, selon les extensions déjà prises en charge. |
| `part_covers[index]` | `files.part_cover_url` | Photo de couverture facultative du fichier principal au même index. JPEG, PNG, BMP, GIF ou WebP. |
| `files_names[index]` | `files.file_name` | Nom facultatif. |
| `part_titles[index]` | `files.part_title` | Titre facultatif de la partie. |
| `positions[index]` | `files.position` | Position facultative. |
| `media_lengths[index]` | `files.media_length` | Durée facultative. |

Exemple : `files_urls[0]` contient un PDF et `part_covers[0]` sa couverture PNG. Une seule ligne est créée dans `files`. Son `type_id` reste celui du document ; la couverture ne possède pas de type séparé et ne modifie jamais celui du fichier principal.

Les index peuvent être non consécutifs. Les couvertures sont stockées sur S3 sous `images/works/{work_id}/covers/` avec un nom unique. Sans couverture, `part_cover_url` reste `null` en base ; la ressource `File` conserve son image de remplacement actuelle.

Une couverture sans fichier principal au même index, une vidéo ou un fichier qui se présente comme une photo sans en être une provoque une réponse de validation HTTP 422 avant la création de l’œuvre ou des fichiers. Les erreurs de stockage utilisent la réponse HTTP 500 existante.

`/docs/api` décrit les champs ainsi que les réponses de succès, d’erreur et de validation. L’extension `App\Documentation\ApiResponseTypeExtension` conserve l’enveloppe de `BaseController` et laisse Scramble analyser les ressources réelles dans `data`, y compris les listes et la pagination.
