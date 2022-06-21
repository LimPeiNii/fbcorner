<!-- Sign In Form -->
<div class="modal fade p-0" id="sign-in-popup" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content color-2">
            <div class="modal-body d-flex flex-column">
              <button type="button" class="align-self-end btn-close close-button" id="sign-in-close" data-bs-dismiss="modal" aria-label="Close" style="flex-shrink: 0;"></button>
              <form action="../../helpers/sign_in.php" method="POST" id="sign-in-form" class="px-3 pb-3">
                <h2 class="font-bernard text-center mt-4 mb-4">Sign In</h2>
                <div class="nested-wrap">
                <div class="mb-3">
                    <input type="text" style="display: none;" name="submit_form">
                    <label class="form-label required" for="email">Email <span class="required-star">*</span></label>
                    <input type="text" name="email" id="email_signin" class="form-control width-100p">
                    <div class="text-danger"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label required" for="password">Password <span  class="required-star">*</span></label>
                    <input type="password" name="password" id="password_signin" class="form-control">
                    <div class="text-danger"></div>
                </div>
                </div>
                <div>
                <button type="submit" class="btn btn-dark mt-3 width-100p">Sign In</button>
                </div>
                <div class="container gx-0 my-1">
                <div class="row gx-0">
                    <div class="col">
                    <a href="#" style="color: blue;" onclick="$('#sign-in-close').click(); $('#sign-up-button').click();">No Account? Create One</a>
                    </div>
                </div>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<!-- Sign Up Form -->
<div class="modal fade p-0" id="sign-up-popup" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content color-2">
            <div class="modal-body d-flex flex-column">
              <button type="button" class="align-self-end btn-close close-button" id="sign-up-close" data-bs-dismiss="modal" aria-label="Close" style="flex-shrink: 0;"></button>
              <form action="../../helpers/sign_up.php" class="px-3 pb-3" method="POST" id="sign-up-form" enctype="multipart/form-data">
                <h2 class="font-bernard text-center mt-4 mb-3">Sign Up</h2>
                <div class="nested-wrap">
                <div class="mb-2">
                    <input type="text" style="display: none;" name="submit_form_sign_up_store">
                    <label class="form-label required" for="firstname">First Name <span class="required-star">*</span></label>
                    <input type="text" name="firstname" id="firstname" class="form-control">
                    <p class="text-danger"></p>
                </div>
                <div class="mb-2">
                    <label class="form-label required" for="lastname">Last Name</label>
                    <input type="text" name="lastname" id="lastname" class="form-control">
                    <p class="text-danger"></p>
                </div>
                <div class="mb-2">
                    <label class="form-label required" for="email">Email <span class="required-star">*</span></label>
                    <input type="text" name="email" id="email" class="form-control">
                    <p class="text-danger"></p>
                </div>
                <div class="mb-2">
                    <label class="form-label required" for="tel">Contact Number <span class="required-star">*</span></label>
                    <input type="text" name="tel" id="tel" class="form-control" placeholder="E.g. 0123456789 / 012-3456789">
                    <p class="text-danger"></p>
                </div>
                <div class="mb-2">
                    <label class="form-label required" for="password">Password <span class="required-star">*</span></label>
                    <input type="password" name="password" id="password" class="form-control">
                    <p class="text-danger"></p>
                </div>
                <div class="mb-2">
                    <label class="form-label required" for="c_password">Confirm Password <span class="required-star">*</span></label>
                    <input type="password" name="c_password" id="c_password" class="form-control">
                    <p class="text-danger"></p>
                </div>
                <div class="mb-2">
                    <label class="form-label" for="profile_pic">Profile Picture</label>
                    <input type="file" name="profile_pic" id="profile_pic" class="form-control">
                    <p class="text-danger"></p>
                </div>
                </div>
                <div>
                <button type="submit" class="btn btn-dark mt-3 w-100">Sign Up</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>