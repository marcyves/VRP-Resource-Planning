# Prise en main rapide — contexte médical

Guide court LaTeX (~12–16 pages) pour le profil **`medical`** uniquement (structures, prestations, séances, patients).

## Compiler

```bash
cd documentation/manuel-prise-en-main-medical
make                    # → prise-en-main-medical-vrp.pdf
```

## Captures (compte entreprise medical)

```bash
npm install puppeteer-core   # depuis manuel-utilisateur ou ici
node scripts/capture-screenshots.mjs
make
```

Compte local par défaut : entreprise « Matthieu » (`terminology_profile = medical`).

Variables : `VRP_SCREENSHOT_EMAIL`, `VRP_SCREENSHOT_PASSWORD`, `VRP_SCREENSHOT_BASE`.

## Contenu

1. Contexte et vocabulaire médical
2. Démarrage en 5 minutes
3. Organisation (prestations → structure → séance → patient)
4. Journée type (agenda, facturation `#billing`, trésorerie)
5. Aide-mémoire et dépannage

## Voir aussi

- [Libellés médical](../fr/libelles-medical.md)
- [Manuel utilisateur complet](../manuel-utilisateur/README.md)
