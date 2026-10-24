# Externí přihlášení (slamb/externallogin)

Registrace a přihlášení do phpBB přes existující účet Google, Microsoft nebo Facebook (Meta).
Správce v ACP zadá jen ID klienta a tajný klíč; zbytek se chová jako běžná registrace (podmínky, režim aktivace, skupiny).

- phpBB 3.3.19+, PHP 8.5+, GPL-2.0-only
- Nastavení: ACP → Obecné → Komunikace s klientem → Externí přihlášení

## Zprovoznění Googlu

Potřebuješ účet Google a fórum na HTTPS.

1. Otevři [Google Cloud Console](https://console.cloud.google.com/) a vytvoř projekt (výběr projektu nahoře → *Nový projekt*).
2. Otevři **Google Auth Platform** (<https://console.cloud.google.com/auth/overview>) a klikni na *Začínáme*.
   - **Informace o aplikaci:** název aplikace (uvidí ho návštěvníci na přihlašovací obrazovce Googlu) a e-mail podpory.
   - **Cílová skupina:** *Externí*.
   - **Kontaktní údaje:** tvůj e-mail. Souhlas s podmínkami a *Vytvořit*.
3. V sekci **Branding** vyplň vše, co Google chce; **bez toho nejde v kroku 7 aplikaci publikovat**:
   - název aplikace, e-mail podpory uživatelů a kontaktní e-mail vývojáře
   - **Doména aplikace:** domovská stránka fóra, odkaz na zásady ochrany soukromí (`https://example.com/ucp.php?mode=privacy`) a na podmínky služby (`https://example.com/ucp.php?mode=terms`)
   - **Autorizované domény:** doména fóra bez `https://` (např. `example.com`)
   - Ulož. Pokud Google chce ověřit doménu, udělej to v Google Search Console (zdarma, DNS záznam nebo soubor).
4. V sekci **Klienti → Vytvořit klienta**:
   - **Typ aplikace:** *Webová aplikace*
   - **Název:** libovolný, např. název fóra (vidíš ho jen ty)
   - **Autorizované identifikátory URI pro přesměrování:** zkopíruj *Přesměrovací adresu* ze stránky rozšíření v ACP (vypadá jako `https://example.com/app.php/externallogin/google/callback`) a vlož ji přesně.
   - Klikni na *Vytvořit*.
5. Zkopíruj **ID klienta** a **Tajný klíč klienta** (Google může tajný klíč ukázat jen jednou; při ztrátě přidej na stránce klienta nový).
6. Obojí vlož do stránky rozšíření v ACP, u *Zapnout* zvol *Ano* a odešli.
7. V sekci **Cílová skupina** klikni na **Publikovat aplikaci** (stav *V produkci*). Dokud je stav *Testování*, přihlásí se jen testovací uživatelé, které tam uvedeš. Rozšíření žádá jen základní oprávnění `openid email profile`, takže Google nevyžaduje ověřovací kontrolu.

Konzole Googlu se občas mění, názvy menu se tak mohou lehce lišit.

## Zprovoznění Microsoftu

Funguje s osobními účty Microsoft i s pracovními a školními. Potřebuješ zdarma tenant Microsoft Entra (pokud ho nemáš, administrační centrum Entra tě při prvním přihlášení vyzve k jeho vytvoření).

1. Otevři [administrační centrum Microsoft Entra](https://entra.microsoft.com/) a přejdi na *Entra ID → Registrace aplikací → Nová registrace*.
2. **Název:** libovolný, např. název fóra; návštěvníci ho uvidí na schvalovací obrazovce.
3. **Podporované typy účtů:** *Účty v jakémkoli organizačním adresáři a osobní účty Microsoft*.
4. **Identifikátor URI přesměrování:** platforma *Web* a vlož *Přesměrovací adresu* ze stránky rozšíření v ACP (`https://example.com/app.php/externallogin/microsoft/callback`). Klikni na *Zaregistrovat*.
5. Na stránce *Přehled* zkopíruj **ID aplikace (klienta)**.
6. V *Certifikáty a tajné klíče → Nový tajný klíč klienta* zvol nejdelší platnost a hned zkopíruj **Hodnotu** (ne *ID tajného klíče*; hodnota se zobrazí jen jednou).
7. Obojí vlož do stránky rozšíření v ACP, u *Zapnout* zvol *Ano* a odešli.

Tajný klíč vyprší (nejdéle za 24 měsíců). Poznamenej si datum a včas vytvoř nový, jinak přestane přihlášení přes Microsoft fungovat. E-mail z Microsoftu se nikdy nepovažuje za ověřený, takže registrace přes Microsoft se nikdy sama nepropojí se stávajícím účtem a řídí se běžným nastavením aktivace.

## Zprovoznění Facebooku (Meta)

1. Otevři [Meta for Developers](https://developers.facebook.com/apps/), přihlas se a případně se zaregistruj jako vývojář.
2. *Vytvořit aplikaci*: název aplikace, kontaktní e-mail, případ použití *Ověřování uživatelů a žádání o data pomocí Facebook Login*. Pokud se zeptá na typ aplikace, zvol *Consumer*.
3. Na stránce *Přizpůsobit* tohoto případu použití zkontroluj, že je přidané oprávnění **email** (`public_profile` je výchozí). Stačí standardní přístup, schvalování aplikace není potřeba.
4. V *Facebook Login → Nastavení* vlož *Přesměrovací adresu* ze stránky rozšíření v ACP do **Valid OAuth Redirect URIs** (`https://example.com/app.php/externallogin/facebook/callback`). Ulož.
5. V *Nastavení aplikace → Základní*:
   - zkopíruj **ID aplikace** a **Tajný klíč aplikace** (tlačítko *Zobrazit*),
   - vyplň **URL zásad ochrany soukromí** (`https://example.com/ucp.php?mode=privacy`) a kategorii,
   - u **Smazání uživatelských dat** zvol *URL zpětného volání pro smazání dat* a vlož adresu pro smazání dat ze stránky rozšíření v ACP (`https://example.com/app.php/externallogin/facebook/deletion`). Ulož.
6. ID aplikace (jako ID klienta) a tajný klíč vlož do stránky rozšíření v ACP, u *Zapnout* zvol *Ano* a odešli.
7. Přepni aplikaci z *Development* do režimu **Live** (přepínač nahoře v přehledu). Ve vývojovém režimu se přihlásí jen lidé s rolí v aplikaci.

E-mail z Facebooku se nikdy nepovažuje za ověřený a Facebook ho nemusí poslat vůbec; registrační formulář se na něj pak zeptá. Žádost o smazání od Meta odstraní jen propojení Facebooku s účtem na fóru; samotný účet na fóru zůstává.

Panel Meta se často mění, názvy menu se tak mohou lehce lišit.

Ikony značek: [Font Awesome Free](https://fontawesome.com/) 7 (CC BY 4.0).
