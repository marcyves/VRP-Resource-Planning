# Manuel utilisateur VRP Plan — compilation PDF
MAIN = main
OUTPUT = manuel-utilisateur-vrp
LATEX = pdflatex
LATEXFLAGS = -interaction=nonstopmode -halt-on-error

.PHONY: all pdf clean screenshots

all: pdf

pdf: $(OUTPUT).pdf

$(OUTPUT).pdf: $(MAIN).tex macros.tex metadata.tex chapters/*.tex screenshots/*
	$(LATEX) $(LATEXFLAGS) $(MAIN).tex
	$(LATEX) $(LATEXFLAGS) $(MAIN).tex
	mv $(MAIN).pdf $(OUTPUT).pdf

screenshots:
	node scripts/capture-screenshots.mjs

clean:
	rm -f $(MAIN).aux $(MAIN).log $(MAIN).out $(MAIN).toc $(MAIN).lof $(MAIN).lot
	rm -f $(MAIN).bcf $(MAIN).run.xml $(MAIN).fls $(MAIN).fdb_latexmk
