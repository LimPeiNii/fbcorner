<footer class="color-2 text-black py-5 d-flex justify-content-center mt-auto">
  <div class="row-wrapper px-4 d-flex flex-column" style="max-width: 80rem;">
  <div class="row mb-3">
    <div class="col-md-6 col-12 mb-3">
      <div class="flex-column d-flex">
        <img height="50px" src="../../../assest/Logo.png" class="mb-3 align-self-center">
        <h3 class="pb-2 align-self-center">
            Follow Us On
        </h3>
        <div class="d-flex align-self-center">
            <div class="mx-1">
                <a href="#"><i class="fab fa-twitter rounded-circle text-white font-size-28" style="padding: 0.8rem; background-color: RGB(29, 161, 242);"></i></a>
            </div>
            <div class="mx-1">
                <a href="#"><i class="fab fa-facebook-f rounded-circle text-white font-size-28" style="padding: 0.8rem; padding-left: 19.43px; padding-right: 19.3px; background-color: RGB(0, 112, 230);"></i></a>
            </div>
            <div class="mx-1">
                <a href="#"><i class="fab fa-youtube rounded-circle text-white font-size-28" style="padding: 0.8rem; padding-left: 0.75rem; padding-right: 0.65rem; background-color: #ff0000;"></i></a>
            </div>
            <div class="mx-1">
                <a href="#"><i class="fab fa-instagram rounded-circle text-white" style="padding-bottom: 0.7rem; padding: 0.58rem; background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); padding-left: 0.8rem; padding-right: 0.8rem; font-size: 35px;"></i></a>
            </div>
            <div class="mx-1">
                <a href="#"><i class="fab fa-linkedin-in rounded-circle text-white font-size-28" style="padding-bottom: 0.7rem; padding: 0.645rem;background-color: RGB(14, 104, 156); padding-left: 0.77rem;padding-right: 0.77rem; font-size: 33px;"></i></a>
            </div>
        </div>
        <div class="mt-4 d-flex flex-column align-self-center">
            <h3 class="pb-2 align-self-center">
                Contacts
            </h3>
            <p class="text-muted font-lora">
                Email: thefbcorner@gmail.com<br>
                Phone: +1 (0) 000 0000 001<br>
                Fax: +1 (0) 000 0000 002
            </p>
        </div>
    </div>
    </div>
    <div class="col-md-6 col-12" style="padding-left: 4rem; padding-right: 4rem;">
      <div class="d-flex flex-column align-self-center">
        <h3 class="align-self-center">
            Contact Us
        </h3>
        <div data-form-alert="" hidden="" id="footer-alert">Thanks for filling out the form!</div>
        <form action="../../helpers/homepage.php" method="post" id="footer-form">
            <input type="hidden" name="send-contact-form">
            <div class="mb-2">
                <input type="text" class="border-0 form-control rounded-0" name="footer-name" placeholder="Name" data-form-field="Name" required id="footer-name" style="height: 50px;">
            </div>
            <div class="mb-2">
                <input type="text" class="border-0 form-control rounded-0" name="footer-phone" placeholder="Phone" data-form-field="Phone" id="footer-phone" style="height: 50px;">
            </div>
            <div class="mb-2">
                <textarea type="text" class="border-0 py-3 form-control rounded-0" name="footer-message" placeholder="Message" rows="3" data-form-field="Message" id="footer-message" required></textarea>
            </div>
            <div>
                <span>
                    <button type="submit" class="btn btn-form btn-dark w-100" id="footer-submit"><span class="spinner-border spinner-border-sm me-3 d-none" id="footer-spinner" role="status" aria-hidden="true"></span>Send</button>
                </span>
            </div>
        </form>
    </div>
    </div>
  </div>
  <p class="text-muted align-self-center mb-0 mt-4" style="padding-left: 4rem; padding-right: 4rem;">Copyright &copy; <b class="text-dark">F&amp;B Corner</b>. All Rights Reserved</p>
  </div>
</footer>