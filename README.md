# Connect 4
Pour le déployer automatiquement avec les actions github en FTP sur un serveur O2switch, on crée un fichier main.yml dans le dossier .github/workflows/ avec le contenu suivant:
```yml
name: Deploy to FTP

on:
  push:
    branches:
      - master

jobs:
    deploy:
        runs-on: ubuntu-latest
    
        steps:
        - name: Checkout
        uses: actions/checkout@v2
    
        - name: Deploy
        uses: SamKirkland/FTP-Deploy-Action@3.1.1
        with:
            server: ftp.o2switch.net
            username: ${{ secrets.FTP_USERNAME }}
            password: ${{ secrets.FTP_PASSWORD }}
            server-dir: /www/monsite.com/connect4
            local-dir: .
```

