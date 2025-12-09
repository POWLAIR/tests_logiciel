package exemple;

import java.util.Map;
import java.util.HashMap;
import java.lang.Double;

public class Panier {
    public record Ligne(String nom, int qte, double prixHT) { };

    public void ajouter(Ligne ligne) {
        var existante = this.contenu.get(ligne.nom);

        if(existante != null && existante.prixHT == ligne.prixHT)
        {
            ligne = new Ligne(ligne.nom, existante.qte+ligne.qte, getTotalHT());
        }
        if(ligne.qte < 0)
        {
            throw new IllegalArgumentException("La quantité finale dans le panier ne peut pas être négative.");
        }
        else if(ligne.qte == 0)
        {
            this.contenu.remove(ligne.nom);
        }
        else 
        {
            this.contenu.put(ligne.nom, ligne);
        }
    }
    public boolean estVide()
    {
        return this.contenu.isEmpty();
    }
    public double getTotalHT()
    {
        return this.contenu
            .values()
            .stream()
            .map(l -> l.prixHT * l.qte)
            .reduce(.0, Double::sum);
    }
    public double getTotalTTC(double taux)
    {
        return this.getTotalHT() * (1.0 + taux);
    }
    private Map<String, Ligne> contenu = new HashMap<>();
}
