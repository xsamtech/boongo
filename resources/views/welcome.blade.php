{{-- @extends('layouts.guest')

@section('guest-content')

		<!-- SECTION -->
		<div class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->

@endsection --}}
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}">
	<link rel="manifest" href="{{ asset('assets/img/favicon/site.webmanifest') }}">
	<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/electro/bootstrap/css/bootstrap.min.css') }}"/>
	<style>
		/* Sticky footer styles 
		-------------------------------------------------- */
		html { position: relative; min-height: 100%; }
		body { margin-bottom: 60px; /* Margin bottom by footer height */ }
		.footer { position: absolute; bottom: 0; width: 100%; 
					height: 60px; /* Set the fixed height of the footer here */
					line-height: 60px; /* Vertically center the text there */
					background-color: #f5f5f5; }
		/* Custom page CSS
		-------------------------------------------------- */
		/* Not required for template or sticky footer method. */
		.container { width: auto; max-width: 680px; padding: 0 15px; }
	</style>
	<title>Bibliothèque numérique</title>
</head>
<body>
		<main role="main" class="container">
			<h1 class="mt-5">Annonce du lancement d’une bibliothèque numérique au standard industriel</h1>
			<p class="lead">
				Nous avons le plaisir de vous annoncer le lancement officiel de notre <strong>bibliothèque numérique de nouvelle génération, conçue selon les standards industriels les plus avancés. Cette plateforme innovante a pour vocation de rendre accessibles, à un large public, des contenus de haute valeur documentaire dans un objectif de <span class="text-danger">vulgarisation des données</span></strong>.
			</p>
			<p>Notre bibliothèque propose un vaste catalogue comprenant :</p>
			<ul class="list-group-flush">
				<li class="list-group-item"><strong>Ouvrages</strong> de référence et de culture générale</li>
				<li class="list-group-item"><strong>Journaux</strong> historiques et contemporains</li>
				<li class="list-group-item"><strong>Magazines</strong> spécialisés et grand public</li>
				<li class="list-group-item"><strong>Revues</strong> scientifiques et techniques</li>
				<li class="list-group-item"><strong>Cartes</strong> géographiques et thématiques</li>
				<li class="list-group-item">Et bien d’autres ressources encore</li>
			</ul>
			<p>Grâce à une interface intuitive, une recherche optimisée et une compatibilité multi-supports, cette bibliothèque numérique s’adresse aussi bien aux chercheurs, enseignants, étudiants, qu’aux curieux désireux d’approfondir leurs connaissances.</p>
			<p>Nous vous invitons à découvrir dès maintenant cet espace de savoir, pensé pour démocratiser l’accès à l’information et valoriser la richesse documentaire à travers un traitement numérique moderne et respectueux des normes internationales.</p>
			<p class="lead">Rejoignez-nous pour faire de la connaissance un bien partagé.</p>
			<hr class="my-3">
			<p><u>Contact</u> : <strong>+243 855 273 394</strong></p>
			<p><u>E-mail</u> : <strong><a href="mailto:contact@boongo7.com">contact@boongo7.com</a></strong></p>
			<p><u>Adresse Physique</u> : <strong>Silikin Village - Concession COTEX / 63 Avenue Colonel Mondjiba - Kinshasa/Gombe</strong></p>
		</main>

		<footer class="footer">
			<div class="container">
				<span class="d-inline-block me-5 text-muted">&copy; {{ date('Y') }} Reborn Tous droits réservés.</span> <span class="d-inline-block text-muted">Designed by <a href="https://xsamtech.com">Xsam Technologies</a></span>
			</div>
		</footer>
</body>
</html>