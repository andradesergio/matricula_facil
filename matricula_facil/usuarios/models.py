from django.db import models
from django.contrib.auth.models import User

class Aluno(models.Model):
   
    usuario = models.OneToOneField(User, on_delete=models.CASCADE, related_name='aluno', null=True, blank=True)
    
    
    nome_completo = models.CharField(max_length=100)
    data_nascimento = models.DateField(null=True, blank=True)
    cpf = models.CharField(max_length=15, unique=True)
    nome_mae = models.CharField(max_length=100)
    telefone = models.CharField(max_length=20)
    status_matricula = models.BooleanField(default=False)
    
  
    certidao_nascimento = models.FileField(upload_to='certidoes/', null=True, blank=True)
    arquivo_identidade = models.FileField(upload_to='identidades/', null=True, blank=True)
    comprovante_residencia = models.FileField(upload_to='comprovantes/', null=True, blank=True)
    foto_3x4 = models.ImageField(upload_to='fotos_3x4/', null=True, blank=True)

    def __str__(self):
        return self.nome_completo