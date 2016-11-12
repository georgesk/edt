UiSources = $(shell ls *.ui)
UiFiles   = $(patsubst %.ui, Ui_%.py, $(UiSources))
PYUIC     = pyuic5

all: $(UiFiles)

Ui_%.py: %.ui
	$(PYUIC) -o $@ $<

clean:
	rm -f *~
	rm -f $(UiFiles)

.PHONY: all clean
