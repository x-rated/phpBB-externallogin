# External login (slamb/externallogin)

Sign up and log in to phpBB with an existing Google, Microsoft or Facebook account (Meta).
Admins only paste a client ID and a client secret per provider; everything else works like a regular registration (terms, activation mode, groups).

- phpBB 3.3.19+, PHP 8.5+, GPL-2.0-only
- Settings: ACP → General → Client communication → External login
- Members link and unlink services in UCP → Profile → Linked services

## Setting up Google

You need a Google account and the forum running on HTTPS.

1. Open the [Google Cloud Console](https://console.cloud.google.com/) and create a project (project picker at the top → *New project*).
2. Open **Google Auth Platform** (<https://console.cloud.google.com/auth/overview>) and click *Get started*.
   - **App information:** app name (shown to visitors on the Google consent screen) and your support e-mail.
   - **Audience:** *External*.
   - **Contact information:** your e-mail. Accept the policy and click *Create*.
3. Open **Branding** and fill in everything Google asks for; **publishing in step 7 is not possible until this is complete**:
   - app name, user support e-mail and developer contact e-mail
   - **App domain:** your forum's home page, privacy policy link (`https://example.com/ucp.php?mode=privacy`) and terms of service link (`https://example.com/ucp.php?mode=terms`)
   - **Authorized domains:** your forum's domain without `https://` (e.g. `example.com`)
   - Click *Save*. If Google asks you to verify the domain, do it in Google Search Console (free, DNS record or file).
4. Open **Clients → Create client**:
   - **Application type:** *Web application*
   - **Name:** anything, e.g. your forum name (only you see it)
   - **Authorized redirect URIs:** copy the *Redirect address* from the extension's ACP page (it looks like `https://example.com/app.php/externallogin/google/callback`) and paste it exactly.
   - Click *Create*.
5. Copy the **Client ID** and **Client secret** (Google may show the secret only once; if you lose it, add a new one on the client's page).
6. Paste both into the extension's ACP page, set *Enable* to *Yes* and submit.
7. Open **Audience** and click **Publish app** (status *In production*). While the status is *Testing*, only the test users you list there can sign in. The extension requests only the basic `openid email profile` scopes, so Google does not require a verification review.

Google's console changes now and then, so menu names may differ slightly.

## Setting up Microsoft

Works with personal Microsoft accounts and work or school accounts. You need a free Microsoft Entra tenant (a personal Microsoft account without one is asked to create it on first sign-in at the Entra admin center).

1. Open the [Microsoft Entra admin center](https://entra.microsoft.com/) and go to *Entra ID → App registrations → New registration*.
2. **Name:** anything, e.g. your forum name; visitors see it on the consent screen.
3. **Supported account types:** *Accounts in any organizational directory and personal Microsoft accounts*.
4. **Redirect URI:** platform *Web*, and paste the *Redirect address* from the extension's ACP page (`https://example.com/app.php/externallogin/microsoft/callback`). Click *Register*.
5. On the *Overview* page copy the **Application (client) ID**.
6. Open *Certificates & secrets → New client secret*, pick the longest expiry and copy the **Value** right away (not the *Secret ID*; the value is shown only once).
7. Paste both into the extension's ACP page, set *Enable* to *Yes* and submit.

The client secret expires (24 months at most). Note the date and create a new one before then, otherwise Microsoft sign-in stops working. Microsoft e-mail addresses are never treated as verified, so a Microsoft sign-up never links itself to an existing account and follows your normal activation setting.

## Setting up Facebook (Meta)

Facebook is by far the fiddliest of the three. Meta requires an app review for the e-mail permission, checks your privacy policy page with its own crawler and only lets people with a role in the app sign in until the app is **Live**. Plan an evening for it, do the steps in this order, and read the troubleshooting list at the end before you start. Meta's dashboard changes often, so menu names may differ slightly (the dashboard follows your Facebook language).

### 1. Create the app

1. Open [Meta for Developers](https://developers.facebook.com/apps/), sign in and register as a developer if asked.
2. *Create app*: app name, contact e-mail, use case **Authenticate and request data from users with Facebook Login**. Choose the app type *Consumer* if asked. If Meta asks for a business portfolio, pick your own verified one (you need it later for the review).

### 2. Permissions (use case)

3. **Add the e-mail permission before you submit anything for review**, otherwise it is missing from the submission and you have to send it again. Open *Use cases → Customize* of that use case and make sure both **public_profile** and **email** are added. Click *Add* next to **email** if it is not there yet. Without it Facebook returns no e-mail address to normal users.
4. The permissions are not usable by the public until Meta approves them (see *Review* below). People with a role in the app (admin, developer, tester) can use them immediately, which is why your own tests work even when the review is not done.

### 3. Redirect address

5. Open the same use case's **Settings** (*Use cases → Customize → Settings*, the *Facebook Login* settings) and paste the *Redirect address* from the extension's ACP page into **Valid OAuth Redirect URIs**. Copy it exactly as the ACP shows it, for example `https://www.example.com/externallogin/facebook/callback` (the form with `app.php` appears if your forum does not use clean URLs). `www` and `https` count. Save.
6. If you want to check it, the *Redirect URI Validator* in the same settings tells you whether an address is accepted. An **empty** field produces the misleading error "domain is not included in the app's domains".

### 4. Basic settings

7. Open *App settings → Basic* and fill in all of these, then click **Save Changes** (it is easy to leave without saving):
   - **App domains**: your domain without `https://` and without `www` (`example.com`).
   - **Display name**, **Contact email**, **Category** and the **App icon**.
   - **Privacy policy URL**: `https://www.example.com/ucp.php?mode=privacy`.
   - **Terms of Service URL**: `https://www.example.com/ucp.php?mode=terms`.
   - **User data deletion**: choose *Data deletion callback URL* and paste the *Data deletion callback URL* from the extension's ACP page (`https://www.example.com/externallogin/facebook/deletion`).
   - Under *Add platform → Website*, set **Site URL** to `https://www.example.com/`.
   - The branding fields and the business portfolio may be marked as required; fill them in too.
8. Copy the **App ID** and the **App secret** (click *Show*). Paste them into the extension's ACP page as client ID and secret, set *Enable* to *Yes* and submit.

### 5. Let Meta read your pages

Meta fetches the privacy policy URL with its own crawler (`facebookexternalhit`, network AS 32934) from abroad. It must get a normal **HTTP 200**. If a firewall, Cloudflare rule, geo-blocking or bot protection answers with a challenge, a block or an error page, Meta reports "You must provide a valid Privacy Policy URL" although the field is filled in correctly.

- Test it at the [Sharing Debugger](https://developers.facebook.com/tools/debug/): paste the privacy policy URL and click *Debug* / *Scrape Again*. You want response code 200. "Bad Response Code" means Meta is being stopped.
- The privacy and terms pages must be readable by guests (not only by logged-in members).
- Cloudflare example: if you have a rule that challenges visitors from outside your country on `.php` pages, add an exception for Meta, for instance `... and ip.src.asnum ne 32934`, or put a *Skip* rule for `ip.src.asnum eq 32934` before it. The log of the web server shows what Meta received; look for `facebookexternalhit`.

### 6. Review

9. Open *Review* in the left menu. Typically you go through three parts:
   - **Testing**: Meta wants to see that the permissions were really used. Make a test sign-in with your own account and, if the page asks for it, call the permission once (for example in the Graph API Explorer with `me?fields=id,name,email`).
   - **Verification**: verify the business (the business portfolio you picked in section 1). Meta may require this before it approves the e-mail permission or lets you publish.
   - **App Review**: submit **public_profile** and **email**. Give the reviewer short instructions: where the sign-in button is (login page and registration page), a test account if the reviewer needs one, and that the e-mail address is used to create the forum account. Describe honestly what you do with the data.
10. Submit. Meta says reviews take up to 20 days; in practice it may take hours or days. Check the status under *Review → App Review* until both permissions show **Approved**. Until then normal visitors sign in without an e-mail address and the registration form asks for it.

### 7. Publish (go Live)

11. Open *Publish* (*Zveřejnit*) in the left menu. The *Publish* button at the bottom right stays grey until everything on the page is complete: the data deletion URL must be set, the privacy policy URL must be valid (see section 5) and the use case must be reviewed. Click each row and finish what it asks for.
12. Click **Publish**. The app status changes from *Unpublished* / *Development* to **Live**. In development mode only people with a role in the app can sign in; everybody else gets "App not active".

### 8. Test as a normal visitor

Your own account has a role in the app and always works. Test the final result with a Facebook account **without any role** (best in a private window), once for sign-in and once for a new registration.

### Troubleshooting

| What you see | Cause and fix |
| --- | --- |
| "App not active" / "Aplikace není aktivní" | The app is still in development mode and the account has no role in it. Publish the app (section 7) or add the person as a tester. |
| "The domain of this URL isn't included in the app's domains" although it is | The **Valid OAuth Redirect URIs** field is empty or the address is wrong (section 3). Check `www`, `https` and `app.php`. |
| "This is an invalid redirect URI" | The address in Valid OAuth Redirect URIs differs from the one the extension sends. Copy it again from the ACP page. |
| "You must provide a valid Privacy Policy URL" | Meta cannot fetch the page (section 5), or the Basic settings were not saved. Use the Sharing Debugger. |
| The sign-in window shows "Submit for review" / "Odeslat kvůli kontrole přihlášení" | One of the requested permissions is not approved yet. Only app admins see the banner; check *Review → App Review* and submit the missing permission. |
| Facebook returns no e-mail | The permission is not approved, or the person has no e-mail on the account. The registration form then asks for the address; this is intended. |
| "An account with this e-mail address already exists" (409) | Intended. A Facebook e-mail is never trusted for linking. The member signs in as usual and links Facebook in *UCP → Profile → Linked services*. |
| *Publish* stays grey | Open every row on the Publish page and finish it: data deletion URL, privacy policy URL, use case review. |

A Facebook e-mail address is never treated as verified and Facebook may send none at all; the sign-up form then asks for it. A deletion request from Meta removes the link between the Facebook user and the forum account; the forum account itself stays.

Brand icons: [Font Awesome Free](https://fontawesome.com/) 7 (CC BY 4.0).
