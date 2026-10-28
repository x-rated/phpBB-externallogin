# Externí přihlášení (slamb/externallogin)

Registrace a přihlášení do phpBB přes existující účet Google, Microsoft nebo Facebook (Meta).
Správce v ACP zadá jen ID klienta a tajný klíč; zbytek se chová jako běžná registrace (podmínky, režim aktivace, skupiny).

- phpBB 3.3.19+, PHP 8.5+, GPL-2.0-only
- Nastavení: ACP → Obecné → Komunikace s klientem → Externí přihlášení
- Členové si služby propojují a odpojují v UCP → Profil → Propojené služby

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

Facebook je z těch tří nejpracnější. **Meta si uložené hodnoty ne vždy zapamatuje**: po každém uložení stránku obnov a zkontroluj, že hodnoty zůstaly (Valid OAuth Redirect URIs, Domény aplikace, URL pro smazání dat, URL zásad), a před zveřejněním je zkontroluj ještě jednou. Meta vyžaduje schválení (review) oprávnění pro e-mail, zásady ochrany soukromí kontroluje vlastním robotem a dokud není aplikace **Live**, pustí dovnitř jen lidi s rolí v aplikaci. Počítej s celým večerem, dělej kroky v tomto pořadí a než začneš, přečti si na konci seznam častých potíží. Panel Meta se často mění a řídí se jazykem tvého Facebooku, takže se názvy menu mohou lehce lišit.

### 1. Vytvoření aplikace

1. Otevři [Meta for Developers](https://developers.facebook.com/apps/), přihlas se a případně se zaregistruj jako vývojář.
2. *Vytvořit aplikaci*: název, kontaktní e-mail, případ použití **Ověřování uživatelů a žádání o data pomocí Facebook Login**. Pokud se zeptá na typ aplikace, zvol *Consumer*. Když Meta chce business portfolio, vyber své ověřené (budeš ho potřebovat u review).

### 2. Oprávnění (případ použití)

3. **Oprávnění pro e-mail přidej dřív, než cokoli odešleš do review**, jinak v odeslání chybí a musíš ho posílat znovu. V *Případy použití → Přizpůsobit* u tohoto případu zkontroluj, že jsou přidaná obě oprávnění **public_profile** i **email**. Pokud u **email** chybí, klikni na *Přidat*. Bez něj Facebook běžným uživatelům e-mail nevrátí.
4. Veřejnost smí tato oprávnění používat, až je Meta schválí (viz *Review* níže). Lidé s rolí v aplikaci (admin, vývojář, tester) je mají hned, proto tvoje vlastní testy fungují i bez dokončeného review.

### 3. Přesměrovací adresa

5. V nastavení stejného případu použití (*Případy použití → Přizpůsobit → Nastavení*, nastavení *Facebook Login*) vlož *Přesměrovací adresu* ze stránky rozšíření v ACP do **Valid OAuth Redirect URIs**. Zkopíruj ji přesně tak, jak ji ukazuje ACP, například `https://www.example.com/externallogin/facebook/callback` (tvar s `app.php` se objeví, pokud fórum nepoužívá čisté URL). Záleží na `www` i `https`. Ulož.
6. Ověřit adresu můžeš v nástroji *Redirect URI Validator* ve stejném nastavení. **Prázdné** pole vyvolá zavádějící hlášku „doména není zahrnuta v doménách aplikace".

### 4. Základní nastavení

7. V *Nastavení aplikace → Základní* vyplň všechno z následujícího a klikni na **Uložit změny** (snadno se odejde bez uložení):
   - **Domény aplikace**: doména bez `https://` a bez `www` (`example.com`).
   - **Zobrazovaný název**, **Kontaktní e-mail**, **Kategorie** a **Ikona aplikace**.
   - **URL zásad ochrany soukromí**: `https://www.example.com/ucp.php?mode=privacy`.
   - **URL podmínek služby**: `https://www.example.com/ucp.php?mode=terms`.
   - **Smazání uživatelských dat**: zvol *URL zpětného volání pro smazání dat* a vlož adresu pro smazání dat ze stránky rozšíření v ACP (`https://www.example.com/externallogin/facebook/deletion`).
   - V *Přidat platformu → Web* nastav **URL webu** na `https://www.example.com/`.
   - Pole značky (branding) a business portfolio mohou být povinná, vyplň i je.
8. Zkopíruj **ID aplikace** a **Tajný klíč aplikace** (tlačítko *Zobrazit*). Vlož je do stránky rozšíření v ACP jako ID klienta a tajný klíč, u *Zapnout* zvol *Ano* a odešli.

### 5. Pusť Metu na své stránky

Meta stahuje URL zásad ochrany soukromí vlastním robotem (`facebookexternalhit`, síť AS 32934) ze zahraničí. Musí dostat normální **HTTP 200**. Pokud firewall, pravidlo Cloudflare, blokování podle zemí nebo ochrana proti botům odpoví challenge stránkou, blokem nebo chybou, Meta hlásí „You must provide a valid Privacy Policy URL", i když je pole vyplněné správně.

- Vyzkoušej to v [Sharing Debuggeru](https://developers.facebook.com/tools/debug/): vlož URL zásad a klikni na *Debug* / *Scrape Again*. Chceš odpověď 200. „Bad Response Code" znamená, že Metu něco zastavuje.
- Stránky se zásadami a podmínkami musí být čitelné i pro hosty (nejen pro přihlášené členy).
- Příklad pro Cloudflare: pokud máš pravidlo, které vyzývá (challenge) návštěvníky mimo tvou zemi na `.php` stránkách, přidej výjimku pro Metu, například `... and ip.src.asnum ne 32934`, nebo před něj dej pravidlo *Skip* pro `ip.src.asnum eq 32934`. Co Meta dostala, ukáže log webserveru; hledej `facebookexternalhit`.

### 6. Review

9. V levém menu otevři *Review*. Obvykle projdeš tři části:
   - **Testování**: Meta chce vidět, že se oprávnění skutečně použila. Udělej testovací přihlášení svým účtem a, pokud to stránka požaduje, zavolej oprávnění jednou (například v Graph API Explorer přes `me?fields=id,name,email`).
   - **Ověření**: ověř firmu (business portfolio, které jsi vybral v oddílu 1). Meta to může vyžadovat před schválením oprávnění pro e-mail nebo před publikací.
   - **Kontrola aplikace**: odešli **public_profile** a **email**. Dej recenzentovi krátké pokyny: kde je tlačítko pro přihlášení (přihlašovací a registrační stránka), testovací účet, pokud ho recenzent potřebuje, a že se e-mail používá k založení účtu na fóru. Popiš poctivě, co s daty děláš.
10. Odešli. Meta uvádí až 20 dní; v praxi to může být několik hodin až dní. Stav sleduj v *Review → Kontrola aplikace*, dokud obě oprávnění nemají **Approved**. Do té doby se běžní návštěvníci přihlásí bez e-mailu a registrační formulář se na něj zeptá.

### 7. Zveřejnění (Live)

11. V levém menu otevři *Zveřejnit*. Tlačítko *Publish* vpravo dole zůstává šedé, dokud není celá stránka kompletní: musí být nastavená URL pro smazání dat, URL zásad ochrany soukromí musí být platná (viz oddíl 5) a případ použití musí projít review. Rozklikni každý řádek a dokonči, co chce.
12. Klikni na **Publish**. Stav aplikace se změní z *Unpublished* / *Development* na **Live**. Ve vývojovém režimu se přihlásí jen lidé s rolí v aplikaci, ostatní dostanou „Aplikace není aktivní".

### 8. Vyzkoušej to jako běžný návštěvník

Tvůj účet má v aplikaci roli a funguje vždy. Konečný výsledek vyzkoušej facebookovým účtem **bez jakékoli role** (nejlépe v anonymním okně), jednou přihlášení a jednou novou registraci.

### Časté potíže

| Co vidíš | Příčina a řešení |
| --- | --- |
| „Aplikace není aktivní" / „App not active" | Aplikace je ještě ve vývojovém režimu a účet nemá roli. Zveřejni aplikaci (oddíl 7) nebo osobu přidej jako testera. |
| „Doména této URL není zahrnuta v doménách aplikace", i když je | Pole **Valid OAuth Redirect URIs** je prázdné nebo je adresa špatně (oddíl 3). Zkontroluj `www`, `https` a `app.php`. |
| „This is an invalid redirect URI" | Adresa ve Valid OAuth Redirect URIs se liší od té, kterou rozšíření posílá. Zkopíruj ji znovu ze stránky v ACP. |
| „You must provide a valid Privacy Policy URL" | Meta stránku nedokáže stáhnout (oddíl 5), nebo se základní nastavení neuložilo. Použij Sharing Debugger. |
| V přihlašovacím okně je „Odeslat kvůli kontrole přihlášení" / „Submit for review" | Některé z požadovaných oprávnění ještě není schválené. Banner vidí jen admini aplikace; zkontroluj *Review → Kontrola aplikace* a odešli chybějící oprávnění. |
| Facebook nevrátí e-mail | Oprávnění není schválené, nebo osoba nemá u účtu e-mail. Registrační formulář se pak na adresu zeptá; je to záměr. |
| „Účet s touto e-mailovou adresou už existuje" (409) | Záměr. E-mail z Facebooku se nikdy nepovažuje za důvěryhodný pro propojení. Člen se přihlásí jako dřív a Facebook si propojí v *UCP → Profil → Propojené služby*. |
| *Publish* zůstává šedé | Otevři všechny řádky na stránce Zveřejnit a dokonči je: URL pro smazání dat, URL zásad, review případu použití. |

E-mail z Facebooku se nikdy nepovažuje za ověřený a Facebook ho nemusí poslat vůbec; registrační formulář se na něj pak zeptá. Žádost o smazání od Meta odstraní jen propojení Facebooku s účtem na fóru; samotný účet na fóru zůstává.

Ikony značek: [Font Awesome Free](https://fontawesome.com/) 7 (CC BY 4.0).
