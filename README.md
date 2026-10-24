# External login (slamb/externallogin)

Sign up and log in to phpBB with an existing Google, Microsoft or Facebook account (Meta).
Admins only paste a client ID and a client secret per provider; everything else works like a regular registration (terms, activation mode, groups).

- phpBB 3.3.19+, PHP 8.5+, GPL-2.0-only
- Settings: ACP → General → Client communication → External login

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

1. Open [Meta for Developers](https://developers.facebook.com/apps/), sign in and register as a developer if asked.
2. *Create app*: app name, contact e-mail, use case *Authenticate and request data from users with Facebook Login*. Choose the app type *Consumer* if asked.
3. Open the use case's *Customize* page and make sure the **email** permission is added (`public_profile` is there by default). Standard access is enough, no app review is needed.
4. Open *Facebook Login → Settings* and paste the *Redirect address* from the extension's ACP page into **Valid OAuth Redirect URIs** (`https://example.com/app.php/externallogin/facebook/callback`). Save.
5. Open *App settings → Basic*:
   - copy the **App ID** and the **App secret** (click *Show*),
   - fill in the **Privacy policy URL** (`https://example.com/ucp.php?mode=privacy`) and a category,
   - set **User data deletion** to *Data deletion callback URL* and paste the *Data deletion callback URL* from the extension's ACP page (`https://example.com/app.php/externallogin/facebook/deletion`). Save.
6. Paste the App ID (as the client ID) and the App secret into the extension's ACP page, set *Enable* to *Yes* and submit.
7. Switch the app from *Development* to **Live** (toggle at the top of the dashboard). In development mode only people with a role in the app can sign in.

A Facebook e-mail address is never treated as verified and Facebook may send none at all; the sign-up form then asks for it. A deletion request from Meta removes the link between the Facebook user and the forum account; the forum account itself stays.

Meta's dashboard changes often, so menu names may differ slightly.

Brand icons: [Font Awesome Free](https://fontawesome.com/) 7 (CC BY 4.0).
