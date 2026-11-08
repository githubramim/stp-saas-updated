from pypdf import PdfReader
r = PdfReader('P:/code/stp-saas-main/docs/Business Requirements Document.pdf')
out = open('P:/code/stp-saas-main/docs/brd.txt','w',encoding='utf-8')
for i,p in enumerate(r.pages):
    out.write('=== PAGE %d ===\n' % (i+1))
    out.write(p.extract_text() or '')
    out.write('\n')
out.close()
print('done', len(r.pages))
