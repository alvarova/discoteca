# LT10 Discoteca 2.0 — rollback

La copia previa está en el servidor como `search.html.bak-20261009T085831`.

Para revertir:

```bash
cp /opt/lampp/htdocs/discoteca/search.html.bak-20261009T085831 /opt/lampp/htdocs/discoteca/search.html
```

No requiere reiniciar Apache: la plantilla se lee en cada solicitud.
