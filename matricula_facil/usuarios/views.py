from django.shortcuts import render, redirect
from django.contrib.auth.models import User
from django.contrib.auth import authenticate, login
from django.contrib import messages
from .models import Aluno

def home(request):
    return render(request, 'home.html')

def login_custom(request):
    if request.user.is_authenticated: 
        return redirect('matricula')

    if request.method == 'POST':
        cpf_digitado = request.POST.get('cpf')
        senha_digitada = request.POST.get('password')
        
        cpf_limpo = ''.join(filter(str.isdigit, cpf_digitado)) if cpf_digitado else ''
        usuario_existe = User.objects.filter(username=cpf_limpo).exists()
        
        if not usuario_existe:
            messages.warning(request, f"O CPF {cpf_digitado} não foi localizado em nossa base do SISSEL. Cadastre-se abaixo.")
            return redirect('cadastro')
            
        user = authenticate(request, username=cpf_limpo, password=senha_digitada)
        if user is not None:
            login(request, user)
            return redirect('matricula')
        else:
            messages.error(request, "Senha incorreta para o CPF informado.")
            
    return render(request, 'registration/login.html')

def cadastro(request):
    if request.user.is_authenticated:
        return redirect('matricula')

    if request.method == 'POST':
        cpf_novo = request.POST.get('new_cpf')
        nome = request.POST.get('nome')
        senha = request.POST.get('new_password')
        
        cpf_limpo = ''.join(filter(str.isdigit, cpf_novo)) if cpf_novo else ''
        
        if User.objects.filter(username=cpf_limpo).exists():
            messages.error(request, "Este CPF já está cadastrado.")
            return redirect('login_custom')
            
        user = User.objects.create_user(username=cpf_limpo, password=senha, first_name=nome)
        login(request, user)
        return redirect('matricula')
        
    return render(request, 'registration/cadastro.html')

def matricula(request):
    if not request.user.is_authenticated:
        messages.error(request, "Por favor, realize o login para acessar a ficha de matrícula.")
        return redirect('login_custom')
        
    if request.method == 'POST':
       
        aluno, created = Aluno.objects.get_or_create(usuario=request.user)
        
     
        aluno.nome_completo = request.POST.get('nome_completo')
        aluno.data_nascimento = request.POST.get('data_nascimento')
        aluno.nome_mae = request.POST.get('nome_mae')
        aluno.telefone = request.POST.get('telefone')
        aluno.cpf = request.user.username  
        aluno.status_matricula = True
        
       
        if 'doc_certidao' in request.FILES:
            aluno.certidao_nascimento = request.FILES['doc_certidao']
        if 'doc_rg' in request.FILES:
            aluno.arquivo_identidade = request.FILES['doc_rg']
        if 'doc_residencia' in request.FILES:
            aluno.comprovante_residencia = request.FILES['doc_residencia']
        if 'doc_foto' in request.FILES:
            aluno.foto_3x4 = request.FILES['doc_foto']
            
        aluno.save()
        messages.success(request, "Matrícula finalizada com sucesso! Gerando comprovante...")
        
       
        url_comprovante_php = f"http://localhost:8080/imprimir.php?cpf={aluno.cpf}"
        return redirect(url_comprovante_php)
        
    return render(request, 'matricula.html')