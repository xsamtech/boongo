
		<!-- SECTION -->
		<div class="section">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-sm-8">
                        <div class="contact-form-wrap">
                            <form id="contact-form" action="assets/php/mail.php" method="POST">
                                <div class="contact-page-form">
                                    <div class="row contact-input">
                                        <div class="col-lg-6 col-md-6 contact-inner">
                                            <input name="name" type="text" placeholder="@lang('miscellaneous.firstname')" id="first-name">
                                        </div>
                                        <div class="col-lg-6 col-md-6 contact-inner">
                                            <input name="lastname" type="text" placeholder="@lang('miscellaneous.lastname')" id="last-name">
                                        </div>
                                        <div class="col-lg-6 col-md-6 contact-inner">
                                            <input type="text" placeholder="@lang('miscellaneous.email')" id="email" name="email">
                                        </div>
                                        <div class="col-lg-6 col-md-6 contact-inner">
                                            <input name="subject" type="text" placeholder="@lang('miscellaneous.public.about.contact.message_subject')" id="subject">
                                        </div>
                                        <div class="col-lg-12 col-md-12 contact-inner contact-message">
                                            <textarea name="message"  placeholder="@lang('miscellaneous.public.about.contact.message_content')"></textarea>
                                        </div>
                                    </div>
                                    <div class="contact-submit-btn text-center">
                                        <button class="submit-btn" type="submit"><i class="bi bi-send me-2"></i>@lang('miscellaneous.send')</button>
                                        <p class="form-messege"></p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->
