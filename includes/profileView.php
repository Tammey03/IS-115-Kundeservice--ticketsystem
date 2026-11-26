<main>
    <section class="profile-page">

        <div class="profile-title">
            <h1>Min profil</h1>
            <p>Din personlige informasjon</p>
        </div>

        <div class="profile-card">

            <div class="profile-header">
                <div class="profile-image" aria-hidden="true">
                    <span><?= e($initials) ?></span>
                </div>

                <div class="profile-name">
    <h2><?= e($fullName ?: $user['username']) ?></h2>
    <p><?= e($profileRoleLabel) ?></p>
</div>
                </div>
            </div>

            <div class="profile-information">

                <?php if ($success !== ''): ?>
                    <div class="profile-success" role="status">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="profile-error" role="alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="profile-row">
                    <span class="profile-label">Brukernavn</span>
                    <span><?= e($user['username']) ?></span>
                </div>

                <div class="profile-row">
                    <span class="profile-label">E-post</span>
                    <span><?= e($user['email']) ?></span>
                </div>

                
<div id="profile-details" <?= $activeForm === 'profile' && $errors ? 'hidden' : '' ?>>

    <div class="profile-row">
        <span class="profile-label">Fornavn</span>
        <span><?= e($firstName) ?></span>
    </div>

    <div class="profile-row">
        <span class="profile-label">Etternavn</span>
        <span><?= e($lastName) ?></span>
    </div>

    <div class="profile-row">
        <span class="profile-label">Telefonnummer</span>
        <span><?= e($phone ?: 'Ikke oppgitt') ?></span>
    </div>

    <div class="profile-row">
        <span class="profile-label">Adresse</span>
        <span><?= e($address ?: 'Ikke oppgitt') ?></span>
    </div>

    <button type="button" class="button" id="edit-profile">
        Rediger profil
    </button>
</div>

<div id="profile-edit" <?= $activeForm === 'profile' && $errors ? '' : 'hidden' ?>>

    <h3>Rediger profil</h3>

    <form method="post" class="profile-edit-form">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="profile">

        <label for="first_name">Fornavn</label>
        <input type="text" id="first_name" name="first_name"
               value="<?= e($firstName) ?>"
               maxlength="100" required>

        <label for="last_name">Etternavn</label>
        <input type="text" id="last_name" name="last_name"
               value="<?= e($lastName) ?>"
               maxlength="100" required>

        <label for="phone">Telefonnummer</label>
        <input type="tel" id="phone" name="phone"
               value="<?= e($phone) ?>" maxlength="20">

        <label for="address">Adresse</label>
        <input type="text" id="address" name="address"
               value="<?= e($address) ?>" maxlength="255">

        <div class="profile-actions">
            <button type="submit" class="button">
                Lagre profil
            </button>

            <button type="button" class="button" id="cancel-profile">
                Avbryt
            </button>
            
        </div>
    </form>
</div>

                <details id="password-details" <?= $activeForm === 'password' && $errors ? 'open' : '' ?>>
                    <summary class="button">Endre passord</summary>

                    <form method="post" class="profile-edit-form">
                        <input type="hidden" name="csrf_token"
                               value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="action" value="password">

                        <label for="current_password">
                            Nåværende passord
                        </label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               autocomplete="current-password"
                               required>

                        <label for="new_password">Nytt passord</label>
                        <input type="password"
                               id="new_password"
                               name="new_password"
                               autocomplete="new-password"
                               minlength="8" required>

                        <label for="confirm_password">
                            Bekreft nytt passord
                        </label>
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               autocomplete="new-password"
                               minlength="8" required>

                        <button type="submit" class="button">
                            Lagre nytt passord
                        </button>
                    </form>
                </details>

            </div>
        </div>
    </section>
</main>