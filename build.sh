#! /bin/bash

file=wasa_kredit.ocmod.zip

rm $file 2>/dev/null

zip -r $file extension/*
